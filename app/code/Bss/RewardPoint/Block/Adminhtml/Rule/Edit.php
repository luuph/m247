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

namespace Bss\RewardPoint\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class edit
 *
 * Bss\RewardPoint\Block\Adminhtml\Rule
 */
class Edit extends Container
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Department edit block
     *
     * @return void
     
     */
    protected function _construct()
    {
        $this->_objectId   = 'rule_id';
        $this->_blockGroup = 'Bss_RewardPoint';
        $this->_controller = 'adminhtml_rule';
        parent::_construct();
        if ($this->registry->registry('rewardpoint_rule')->getId()) {
            $this->buttonList->add(
                'delete',
                [
                    'label'   => __('Delete'),
                    'onclick' => 'deleteConfirm('.json_encode(__('Are you sure you want to do this?'))
                        .','.json_encode($this->getDeleteUrl()).')',
                    'class'   => 'scalable delete',
                    'level'   => -1,
                ]
            );
        }

        $this->buttonList->update('save', 'label', __('Save Rule'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                        ],
                    ],
                ],
            ],
            -100
        );
    }

    /**
     * Get header with Department name
     *
     * @return \Magento\Framework\Phrase
     
     */
    public function getHeaderText()
    {
        if ($this->registry->registry('rewardpoint_rule')->getId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($this->registry->registry('rewardpoint_rule')->getName()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            'bssreward/rule/delete',
            ['rule_id' => $this->registry->registry('rewardpoint_rule')->getId()]
        );
    }
}
