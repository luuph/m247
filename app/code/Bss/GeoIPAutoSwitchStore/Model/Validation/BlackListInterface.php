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
 * @package    Bss_GeoIPAutoSwitchStore
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GeoIPAutoSwitchStore\Model\Validation;

interface BlackListInterface
{
    /**
     * @param string $ip
     * @return $this
     */
    public function setIp($ip);

    /**
     * @param array|string $countries
     * @return $this
     */
    public function setCountries($countries);

    /**
     * @param int $type
     * @return bool|void|string
     */
    public function validate($type);
}
