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
namespace Bss\RewardPoint\Block\Adminhtml;

/**
 * Class rules
 *
 * Bss\RewardPoint\Block\Adminhtml
 */
class Rules extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'rule';
        $this->_headerText = __('Earning Rules');
        parent::_construct();
    }

    /**
     * Prepare button and grid
     *
     * @return \Bss\RewardPoint\Block\Adminhtml\Rules
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('add');
        $addButtonProps = [
            'id' => 'add_new_rule',
            'label' => __('Add New Rule'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->getAddRuleButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Rule' split button
     *
     * @return array
     */
    protected function getAddRuleButtonOptions()
    {
        return [
            [
                'label' => __('Add New Rule Cart'),
                'onclick' => "setLocation('" . $this->getRuleCreateUrl('cart') . "')",
                'default' =>  true
            ],
            [
                'label' => __('Add New Rule Custom'),
                'onclick' => "setLocation('" . $this->getRuleCreateUrl('custom') . "')",
                'default' =>  false
            ]
        ];
    }

    /**
     * Retrieve rule create url by specified rule type
     *
     * @param string $type
     * @return string
     */
    protected function getRuleCreateUrl($type)
    {
        return $this->getUrl('bssreward/rule/new', ['type' => $type]);
    }
}
