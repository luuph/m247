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
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Helper;

use Mageplaza\DeliveryTime\Helper\Data as mpDthelper;

/**
 * Class Data
 * @package Mageplaza\Customize\Helper
 */
class Data extends mpDthelper
{
    const CONFIG_MODULE_PATH = 'mpdeliverytime';

    public function getCutoffTime($store = null) {
        if (!$this->getConfigGeneral('is_enabled_cutoff_time', $store)) {
            return null;
        }
        $time = $this->getConfigGeneral('cutoff_time', $store);
        $time = explode(',', $time);
        array_pop($time);
        return $time;;
    }
}
