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
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BarcodeType
 * @package Mageplaza\PdfInvoice\Model\Source
 */
class Attribute implements OptionSourceInterface
{
    const SKU          = 'sku';
    const PRODUCT_ID   = 'product_id';
    const PRODUCT_NAME = 'product_name';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::SKU => __('SKU'),
            self::PRODUCT_ID  => __('Product ID'),
            self::PRODUCT_NAME  => __('Product Name'),
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptionHash() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }
}
