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
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Helper;

use Mageplaza\Affiliate\Helper\Data as StandardHelper;

/**
 * Class Data
 * @package Mageplaza\AffiliatePro\Helper
 */
class Data extends StandardHelper
{
    /**
     * @param $orderId
     *
     * @return null
     */
    public function getCustomerEmailByOrder($orderId)
    {
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $customer_email = $order->getCustomerEmail();
        if ($customer_email) {
            return $customer_email;
        }

        return null;
    }
}
