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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Model;

use Mageplaza\Shopbybrand\Helper\Data;
use Magento\Catalog\Model\Config as ConfigCatalogCore;

/**
 * Class Config
 * @package Mageplaza\Shopbybrand\Plugin\Model
 */
class Config
{
    /** @var Data */
    protected $helper;

    /**
     * Config constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After plugin for getProductAttributes
     *
     * @param ConfigCatalogCore $subject
     * @param array $result
     * @return array
     */
    public function afterGetProductAttributes(
        ConfigCatalogCore $subject,
        $result
    ) {
        $attributeCode = $this->helper->getAttributeCode();
        if (!in_array($attributeCode, $result) && $this->helper->isEnable()) {
            $result[] = $attributeCode;
        }

        return $result;
    }
}
