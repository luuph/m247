<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Directory;

use Mageplaza\CurrencyFormatter\Helper\Data;

class PriceCurrency
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Constructor with dependency injection.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * Modify the formatPrice method to use 3 decimal places and enforce English formatting.
     *
     * @param \Magento\Directory\Model\PriceCurrency $subject
     * @param \Closure $proceed
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return string
     */
    public function aroundFormat(
        $subject,
        \Closure $proceed,
        $amount,
        $includeContainer = true,
        $precision = 2
    ) {
        if ($this->helperData->isEnabled()) {
            // Enforce 3 decimal places
            $precision = 3;

            // Get the formatted price using the original method
            $result = $proceed($amount, $includeContainer, $precision);

            // Replace Arabic numerals and symbols with English equivalents
            $arabicToEnglishMap = [
                '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
                '٫' => '.', '٬' => '' // Replace Arabic decimal and grouping symbols
            ];

            return strtr($result, $arabicToEnglishMap);
        }

        return $proceed($amount, $includeContainer, $precision);
    }
}
