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

class IsActive implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => 0, 'label' => __('Disable')],
                ['value' => 1, 'label' => __('Enable')]
                ];
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Disable'), 1 => __('Enable')];
    }
}
