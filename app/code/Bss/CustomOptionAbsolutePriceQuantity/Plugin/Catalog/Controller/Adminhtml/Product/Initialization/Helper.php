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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

class Helper
{
    /**
     * Merge product and default options for product
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param array $result
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMergeProductOptions($subject, $result, $productOptions, $overwriteOptions)
    {
        if (!is_array($result)) {
            return [];
        }

        foreach ($result as $optionIndex => $option) {
            $optionId = $option['option_id'];
            $option = $this->overwriteValue($optionId, $option, $overwriteOptions);
            $result[$optionIndex] = $option;
        }

        return $result;
    }

    /**
     * Overwrite values of fields to default, if there are option id and field name in array overwriteOptions
     *
     * @param int $optionId
     * @param array $option
     * @param array $overwriteOptions
     * @return array
     */
    public function overwriteValue($optionId, $option, $overwriteOptions)
    {
        if (isset($overwriteOptions[$optionId])) {
            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite) {
                    // Add logic use default value in two field description.
                    if ('bss_description_option_type' == $fieldName) {
                        $option['is_delete_store_bss_description_option_type'] = 1;
                    }
                    if ('bss_description_option' == $fieldName) {
                        $option['is_delete_store_bss_description_option'] = 1;
                    }
                    // End logic use default value in two field description
                }
            }
        }
        return $option;
    }
}
