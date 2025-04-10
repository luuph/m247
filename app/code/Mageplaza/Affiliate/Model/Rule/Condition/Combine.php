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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Model\Rule\Condition;

use Magento\Framework\DataObject;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine as SalesRuleCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;

/**
 * Class Combine
 * @package Mageplaza\Affiliate\Model\Rule\Condition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var Address
     */
    protected $conditionAddress;


    /**
     * @var Product
     */
    protected $productCondition;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param Address $conditionAddress
     * @param Product $productCondition
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $conditionAddress,
        Product $productCondition,
        array $data = []
    ) {
        $this->conditionAddress  = $conditionAddress;
        $this->productCondition  = $productCondition;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes        = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Found::class,
                    'label' => __('Product attribute combination'),
                ],
                [
                    'value' => Subselect::class,
                    'label' => __('Products subselection')
                ],
                [
                    'value' => SalesRuleCombine::class,
                    'label' => __('Conditions combination')
                ],
                ['label' => __('Cart Attribute'), 'value' => $attributes],
            ]
        );

        $additional           = new DataObject();
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
