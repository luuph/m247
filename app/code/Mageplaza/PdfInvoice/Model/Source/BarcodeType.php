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
class BarcodeType implements OptionSourceInterface
{
    const EAN13     = 'EAN13';
    const ISBN      = 'ISBN';
    const ISSN      = 'ISSN';
    const UPCA      = 'UPCA';
    const UPCE      = 'UPCE';
    const EAN8      = 'EAN8';
    const IMB       = 'IMB';
    const RM4SCC    = 'RM4SCC';
    const KIX       = 'KIX';
    const POSTNET   = 'POSTNET';
    const PLANET    = 'PLANET';
    const C128A     = 'C128A';
    const C128B     = 'C128B';
    const C128C     = 'C128C';
    const EAN128A   = 'EAN128A';
    const EAN128B   = 'EAN128B';
    const EAN128C   = 'EAN128C';
    const C39       = 'C39';
    const C39_PLUS  = 'C39+';
    const C39E      = 'C39E';
    const C39E_PLUS = 'C39E+';
    const S25       = 'S25';
    const S25_PLUS  = 'S25+';
    const I25       = 'I25';
    const I25_PLUS  = 'I25+';
    const I25B      = 'I25B';
    const I25B_PLUS = 'I25B+';
    const CODABAR   = 'CODABAR';
    const CODE11    = 'CODE11';
    const C93       = 'C93';
    const MSI       = 'MSI';
    const MSI_PLUS  = 'MSI+';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::EAN13 => __('EAN13'),
            self::ISBN  => __('ISBN'),
            self::ISSN  => __('ISSN'),

            self::UPCA => __('UPC-A'),
            self::UPCE => __('UPC-E'),
            self::EAN8 => __('EAN-8'),
            self::IMB  => __('Intelligent Mail'),

            self::RM4SCC  => __('Royal Mail 4-state Customer'),
            self::KIX     => __('Royal Mail 4-state Customer (Dutch)'),
            self::POSTNET => __('POSTNET'),
            self::PLANET  => __('PLANET'),

            self::C128A => __('Code 128 A'),
            self::C128B => __('Code 128 B'),
            self::C128C => __('Code 128 C'),

            self::EAN128A => __('UCC/EAN-128 A'),
            self::EAN128B => __('UCC/EAN-128 B'),
            self::EAN128C => __('UCC/EAN-128 C'),

            self::C39       => __('Code 39'),
            self::C39_PLUS  => __('Code 39+'),
            self::C39E      => __('Code 39E'),
            self::C39E_PLUS => __('Code 39E+'),

            self::S25      => __('Standard 2 of 5'),
            self::S25_PLUS => __('Standard 2 of 5+'),

            self::I25       => __('Interleaved 2 of 5'),
            self::I25_PLUS  => __('Interleaved 2 of 5 +'),
            self::I25B      => __('Interleaved 2 of 5 B'),
            self::I25B_PLUS => __('Interleaved 2 of 5 B+'),

            self::MSI      => __('MSI'),
            self::MSI_PLUS => __('MSI+'),

            self::C93     => __('C93'),
            self::CODABAR => __('CODABAR'),
            self::CODE11  => __('CODE11'),
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
