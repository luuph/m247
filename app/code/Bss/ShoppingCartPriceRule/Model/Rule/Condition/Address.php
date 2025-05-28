<?php

namespace Bss\ShoppingCartPriceRule\Model\Rule\Condition;


use Magento\SalesRule\Model\Rule\Condition\Address as SalesRuleAddress;


class Address extends SalesRuleAddress
{
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();


        $attributes = $this->getAttributeOption();


        $attributes['base_subtotal_with_discount'] = __('Subtotal after discount');


        $this->setAttributeOption($attributes);


        return $this;
    }
    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() == 'base_subtotal_with_discount') {
            return 'numeric';
        }


        return parent::getInputType();
    }


    /**
     * Validate attribute
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        $subAfterDiscount = $address->getBaseSubtotal() + $address->getDiscountAmount();
        $address->setData('base_subtotal_with_discount', $subAfterDiscount);
        return parent::validate($address);
    }
}
