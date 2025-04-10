<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Sale\Component;

use Mageplaza\CurrencyFormatter\Helper\Data;

class PurchasedPrice
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
     * Modify the formatPrice method to use 3 decimal places.
     */
    public function aroundFormatPrice($subject, \Closure $proceed, $price)
    {
        if ($this->helperData->isEnabled()) {
            $price = number_format((float)$price, 3, '.', '');
        }
        return $proceed($price);
    }
}
