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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model\Config\Source;

class RuleType implements \Magento\Framework\Option\ArrayInterface
{
    public const RULE_TYE_CART = 'cart';
    public const RULE_TYE_CUSTOM = 'custom';

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => self::RULE_TYE_CART, 'label' => __('Cart')],
                ['value' => self::RULE_TYE_CUSTOM, 'label' => __('Custom')]
                ];
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return [self::RULE_TYE_CART => __('Cart'), self::RULE_TYE_CUSTOM => __('Custom')];
    }
}
