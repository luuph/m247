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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model\Customer\Attribute\Validator;

class FileExtension
{
    /**
     * Fix mass action update attribute type file.
     *
     * @param \Magento\CustomerCustomAttributes\Model\Customer\Attribute\Validator\FileExtension $subject
     * @param \Magento\Eav\Model\Entity\Attribute\AttributeInterface $attribute
     * @return null
     */
    public function beforeValidate($subject, $attribute)
    {
        if ($attribute->getData('frontend_input') === 'file') {
            if ($attribute->getData('file_extensions') === null) {
                $attribute->setData('file_extensions', '');
            }
        }

        return null;
    }
}
