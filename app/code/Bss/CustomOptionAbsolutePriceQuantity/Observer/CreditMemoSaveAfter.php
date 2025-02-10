<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\OptionStockHelper;
use Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReportFactory;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;

class CreditMemoSaveAfter implements ObserverInterface
{
    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReportFactory
     */
    protected $optionQtyReportFactory;

    /**
     * @var OptionStockHelper
     */
    protected $optionStockHelper;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * CreditMemoSaveAfter constructor.
     * @param OptionQtyReportFactory $optionQtyReportFactory
     * @param ModuleConfig $moduleConfig
     * @param OptionStockHelper $optionStockHelper
     */
    public function __construct(
        OptionQtyReportFactory $optionQtyReportFactory,
        ModuleConfig $moduleConfig,
        OptionStockHelper $optionStockHelper
    ) {
        $this->optionQtyReportFactory = $optionQtyReportFactory;
        $this->optionStockHelper = $optionStockHelper;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->moduleConfig->allowQtyReport()) {
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $checkOrderExist = $this->optionQtyReportFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('creditmemo_id', ['eq' =>$creditmemo->getId()]);
            if ($checkOrderExist->getSize() < 1) {
                $data = $this->optionStockHelper->getDataQtyOption($creditmemo->getOrder(), $creditmemo);
                if (!empty($data)) {
                    $this->optionStockHelper->insertDataToStockManageTable($data);
                }
            }
        }
    }
}
