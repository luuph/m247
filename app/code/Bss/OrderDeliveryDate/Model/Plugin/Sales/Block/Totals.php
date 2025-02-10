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
namespace Bss\OrderDeliveryDate\Model\Plugin\Sales\Block;

use Magento\Framework\View\Element\Template;
use Bss\OrderDeliveryDate\Helper\Data;

class Totals extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Totals Constructor
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Init Totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $timeSlotPrice = $this->getOrder()->getTimeSlotPrice();
        $baseTimeSlotPrice = $this->getOrder()->getBaseTimeSlotPrice();
        if ($timeSlotPrice) {
            $this->getParentBlock()->addTotalBefore(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'delivery_time_slot',
                        'strong' => false,
                        'value' => $timeSlotPrice, // extension attribute field
                        'base_value' => $baseTimeSlotPrice,
                        'label' => __('Delivery Time Slot'),
                    ]
                ),
                'grand_total'
            );
        }
        return $this;
    }

    /**
     * Get Source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get Order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }
}
