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
namespace Bss\RewardPoint\Block\Adminhtml\System\Config\Form;

use Magento\Framework\View\Element\Html\Select;

/**
 * Class type date
 *
 * Bss\RewardPoint\Block\Adminhtml\System\Config\Form
 */
class TypeDate extends Select
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 1, 'label' => 'Day'],
            ['value' => 2, 'label' => 'Month'],
            ['value' => 3, 'label' => 'Year'],
        ];
        return $options;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $options =  $this->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }
        return parent::_toHtml();
    }

    /**
     * Set input name
     *
     * @param string $value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
