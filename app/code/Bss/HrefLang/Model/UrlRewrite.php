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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class UrlRewrite
 *
 * @package Bss\HrefLang\Model
 */
class UrlRewrite extends AbstractModel
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init('Bss\HrefLang\Model\ResourceModel\UrlRewrite');
    }
}
