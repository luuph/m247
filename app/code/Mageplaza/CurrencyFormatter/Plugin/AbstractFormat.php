<?php
namespace Mageplaza\CurrencyFormatter\Plugin;
use Mageplaza\CurrencyFormatter\Helper\Data;
class AbstractFormat
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
* Example method that uses the helperData property.
*/
public function formatAmount($amount)
{
if ($this->helperData->isEnabled()) {
return number_format($amount, 3, '.', '');
}
return $amount;
}
}