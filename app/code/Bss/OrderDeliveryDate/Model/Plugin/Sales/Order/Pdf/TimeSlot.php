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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Model\Plugin\Sales\Order\Pdf;

use Bss\OrderDeliveryDate\Helper\Data;

class TimeSlot extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * @return array[]
     */
    public function getTotalsForDisplay()
    {
        $timeSlotPrice = $this->getSource()->getDeliveryTimeSlotPrice();
        $timeSlotPrice = $this->getOrder()->formatPriceTxt($timeSlotPrice);

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        return [
            [
                'amount' => $timeSlotPrice,
                'label' => __($this->getTitle()) . ':',
                'font_size' => $fontSize,
            ]
        ];
    }
}
