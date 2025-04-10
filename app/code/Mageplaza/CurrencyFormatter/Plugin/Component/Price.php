<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Component;

use Mageplaza\CurrencyFormatter\Helper\Data;

class Price
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
     * Modify the getFormattedPrice method to use 3 decimal places.
     */
    public function aroundGetFormattedPrice($subject, \Closure $proceed, $price)
    {
        if ($this->helperData->isEnabled()) {
            $price = number_format((float)$price, 3, '.', '');
        }
        return $proceed($price);
    }
}
