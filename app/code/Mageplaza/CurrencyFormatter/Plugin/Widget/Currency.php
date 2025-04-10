<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Widget;

use Mageplaza\CurrencyFormatter\Helper\Data;

class Currency
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
     * Modify the getFormattedPrice method to use 3 decimal places and enforce English numerals.
     *
     * @param \Magento\Widget\Model\Widget\Instance $subject
     * @param string $result
     * @return string
     */
    public function afterGetFormattedPrice($subject, $result)
    {
        if ($this->helperData->isEnabled()) {
            // Force 3 decimal places
            $formattedResult = number_format((float)$result, 3, '.', '');

            // Replace Arabic numerals with English numerals
            $arabicToEnglishMap = [
                '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
            ];

            return strtr($formattedResult, $arabicToEnglishMap);
        }

        return $result;
    }
}
