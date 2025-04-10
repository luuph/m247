<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Locale;
use Mageplaza\CurrencyFormatter\Helper\Data;
class Format
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
* Example method using the helperData property.
*/
public function afterGetPriceFormat($subject, $result)
{
if ($this->helperData->isEnabled()) {
$result['precision'] = 3; // Set precision to 3 decimal places
}
return $result;
}
}