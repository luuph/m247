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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionTemplate\Model\Rule\Condition;

class Product
{
    /**
     * Check data Categories to add or remove product from template
     * @param \Magento\CatalogRule\Model\Rule\Condition\Product $subject
     * @param bool $result
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function afterValidate(
        \Magento\CatalogRule\Model\Rule\Condition\Product $subject,
        $result,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $attrCode = $subject->getAttribute();
        if ('category_ids' == $attrCode && $model->getData('check_bss_template') == 'ok') {
            return $subject->validateAttribute($model->getCategoryIds());
        }
        return $result;
    }
}
