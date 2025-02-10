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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\Config\Source;

class SaveMode implements \Magento\Framework\Option\ArrayInterface
{
    const UPDATE_ON_SAVE = 0;
    const UPDATE_BY_SCHEDULE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Update on Save'), 'value' => self::UPDATE_ON_SAVE],
            ['label' => __('Update by schedule'), 'value' => self::UPDATE_BY_SCHEDULE]
        ];
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [0 => __('Update on Save'), 1 => __('Update by Schedule')];
    }
}
