<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Model\ResourceModel\Transaction\Grid;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AffiliateUltimate\Helper\Data;
use Mageplaza\AffiliateUltimate\Model\ResourceModel\AbstractCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Mageplaza\AffiliateUltimate\Model\ResourceModel\Transaction\Grid
 */
class Collection extends AbstractCollection
{
    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        Data $helperData,
        $mainTable = 'mageplaza_affiliate_transaction',
        $resourceModel = '\Mageplaza\Affiliate\Model\ResourceModel\Transaction'
    ) {
        $this->_request = $request;
        $this->helperData = $helperData;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $request,
            $helperData,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $fields = ['status', 'created_at'];
        foreach ($fields as $field) {
            $this->addFilterToMap($field, 'main_table.' . $field);
        }
        $this->getSelect()->joinLeft(
            ['campaign' => $this->getTable('mageplaza_affiliate_campaign')],
            'campaign.campaign_id = main_table.campaign_id',
            ['campaign_name' => 'name']
        )->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['email']
        )->joinLeft(
            ['sales_order' => $this->getTable('sales_order')],
            'sales_order.entity_id = main_table.order_id',
            ['order_status' => 'status']
        );
        $this->addDateToFilter()
            ->addStoreToFilter()
            ->addOrderStatusToFilter();

        return $this;
    }
}
