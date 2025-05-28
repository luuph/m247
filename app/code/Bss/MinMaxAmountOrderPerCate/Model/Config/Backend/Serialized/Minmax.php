<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MinMaxAmountOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxAmountOrderPerCate\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

class Minmax extends ArraySerialized
{
    /**
     * @return ArraySerialized
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save()
    {
        $value_pairs = [];
        $options = $this->getValue();
        if (!empty($options)) {
            foreach ($options as $option) {
                if (is_array($option)) {
                    if (isset($option['customer_group_id']) && isset($option['category_id'])) {
                        $value_pairs[] = $option['customer_group_id'] . '/' . $option['category_id'];
                    }
                    if (isset($option['min_sale_amount']) && $option['min_sale_amount']
                        && (isset($option['max_sale_amount']) && $option['max_sale_amount'])) {
                        $offset = $option['min_sale_amount'] - $option['max_sale_amount'];
                        if ($offset > 0) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __("Value of Min Amount can't larger Value of Max Amount")
                            );
                        }
                    }
                    if ((!isset($option['max_sale_amount']) && !isset($option['min_sale_amount']))
                        || ($option['max_sale_amount'] == "" && $option['min_sale_amount'] == "")) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("Value of Min Amount or Max Amount can not be empty ")
                        );
                    }
                }
            }
        }
        $this->validateDuplicate($value_pairs);
        return parent::save();
    }

    /**
     * @param array $value_pairs
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateDuplicate($value_pairs)
    {
        foreach (array_count_values($value_pairs) as $value) {
            if ($value >1) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Duplicate Category vs Category and Customer Group vs Customer Group not allow.")
                );
            }
        }
    }
}
