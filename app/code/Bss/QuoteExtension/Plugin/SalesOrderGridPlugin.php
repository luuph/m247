<?php

namespace Bss\QuoteExtension\Plugin;

use Bss\QuoteExtension\Helper\Data;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class SalesOrderGridPlugin
{
    /**
     * @var Data
     */
    protected $helper;

    protected $moduleManager;

    /**
     * @param Data $helper
     * @param ModuleManager $moduleManager
     */
    public function __construct(Data $helper, ModuleManager $moduleManager)
    {
        $this->helper = $helper;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false): array
    {
        if (!$subject->isLoaded()) {
            $quoteExtensionTable = $subject->getResource()->getTable('quote_extension');
            $salesOrderTable = $subject->getResource()->getTable('sales_order');

            if (version_compare($this->helper->getMagentoVersion(), '2.4.7', '<')
                || !$this->moduleManager->isEnabled('PayPal_Braintree')) {
                $subject->getSelect()->joinLeft(
                    $salesOrderTable,
                    sprintf(
                        'main_table.entity_id = %s.entity_id',
                        $salesOrderTable
                    ),
                    sprintf('%s.entity_id AS sales_order_entity_id', $salesOrderTable),
                );
            }

            $subject->getSelect()->joinLeft(
                $quoteExtensionTable,
                sprintf(
                    '%s.quote_id = %s.backend_quote_id
                        OR %s.quote_id = %s.target_quote',
                    $salesOrderTable,
                    $quoteExtensionTable,
                    $salesOrderTable,
                    $quoteExtensionTable
                ),
                [
                    sprintf('%s.entity_id AS quote_extension_entity_id', $quoteExtensionTable),
                    sprintf('%s.increment_id AS quote_extension_increment_id', $quoteExtensionTable)
                ]
            );
        }
        // TODO: Implement plugin method.
        return [$printQuery, $logQuery];
    }
}