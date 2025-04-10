<?php
namespace Mageplaza\CurrencyFormatter\Plugin\Directory;
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
* Example method that uses the helperData property.
*/
public function someMethod()
{
return $this->helperData->getConfigValue();
}
}