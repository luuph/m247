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
class CodeType implements OptionSourceInterface
{
    const BARCODE = 'barcode';
    const QR_CODE = 'qr_code';
    const NO_CODE = 'no_code';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::BARCODE => __('Barcode'),
            self::QR_CODE  => __('QR code'),
            self::NO_CODE  => __('No Code'),
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
