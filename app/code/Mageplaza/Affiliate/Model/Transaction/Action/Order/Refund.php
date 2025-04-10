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

namespace Mageplaza\Affiliate\Model\Transaction\Action\Order;

use Magento\Framework\Phrase;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\Affiliate\Model\Transaction\AbstractAction;
use Mageplaza\Affiliate\Model\Transaction\Type;

/**
 * Class Refund
 *
 * @package Mageplaza\Affiliate\Model\Transaction\Action\Order
 */
class Refund extends AbstractAction
{
    /**
     * @return float
     */
    public function getAmount()
    {
        return -(float)$this->getObject()->getCommissionAmount();
    }

    /**
     * @return int
     */
    public function getType()
    {
        return Type::COMMISSION;
    }

    /**
     * @param null $transaction
     *
     * @return Phrase
     */
    public function getTitle($transaction = null)
    {
        $param = $transaction === null
            ? '#' . $this->getObject()->getIncrementId()
            : '#' . $transaction->getOrderIncrementId();

        return __('Taken back commission for refunding order %1', $param);
    }

    /**
     * @return array
     */
    public function prepareAction()
    {
        $order           = $this->getOrder();
        $item            = $this->getObject();
        $transactionData = [
            'order_id'              => $order->getOrderId(),
            'order_item_id'         => $item->getId(),
            'order_increment_id'    => $item->getIncrementId(),
            'store_id'              => $item->getStoreId(),
            'campaign_id'           => $item->getCampaignId(),
            'product_sku'           => $item->getSku(),
            'product_qty'           => $item->getQtyAffiliate(),
        ];

        return $transactionData;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        $object = $this->getObject();
        if ($object instanceof Creditmemo) {
            $order = $object->getOrder();
        } else {
            $order = $object;
        }

        return $order;
    }

    /**
     * @return string
     */
    public function getAdditionContent()
    {
        $extraContent = $this->getExtraContent();
        $object       = $this->getObject();
        if ($object instanceof Creditmemo) {
            $extraContent['creditmemo_increment_id'] = $object->getIncrementId();
        }

        return $this->jsonHelper->jsonEncode($extraContent);
    }
}
