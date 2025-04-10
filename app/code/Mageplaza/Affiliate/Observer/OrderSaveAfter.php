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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mageplaza\Affiliate\Helper\Calculation;

/**
 * Class InvoiceSaveAfter
 * @package Mageplaza\Affiliate\Observer
 */
class OrderSaveAfter implements ObserverInterface
{

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * OrderSaveAfter constructor.
     *
     * @param Calculation        $calculation
     */
    public function __construct(
        Calculation $calculation
    ) {
        $this->calculation         = $calculation;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws Zend_Serializer_Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $entityType = $observer->getEvent()->getOrder()->getEntityType();
        if ($entityType !==  Order::ENTITY) {
            foreach ($order->getItems() as $item) {
                if ($item->getAffiliateCommission()) {
                    $item->setIncrementId($order->getIncrementId());
                    if ($entityType ===  Order::ACTION_FLAG_CREDITMEMO){
                        if ($this->calculation->isProcessRefund()) {
                            $this->calculation->calculateCommissionOrder($item, 'affiliate_commission_refunded', 'order/refund');
                        }
                    }else{
                        $this->calculation->calculateCommissionOrder($item, 'affiliate_commission_invoiced',
                            'order/invoice');
                    }

                }
            }
        }
        return $this;
    }
}
