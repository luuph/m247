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
namespace Bss\CustomOptionTemplate\Block\Adminhtml\Template;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Bss_CustomOptionTemplate';
        $this->_controller = 'adminhtml_template';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->remove('reset');

        $this->buttonList->add(
            'save_and_continue',
            [
                'label' => __('Save and Continue'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ],
            ],
            10
        );
        $templateId = $this->getRequest()->getParam('template_id');
        if ($templateId) {
            $this->buttonList->add(
                'save_and_duplicate',
                [
                    'label' => __('Duplicate'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\''.$this->getUrl('*/*/duplicate', ['template_id' => $templateId]).'\')'
                ],
                20
            );

            $this->buttonList->add(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\''.$this->getUrl('*/*/delete', ['template_id' => $templateId]).'\')'
                ],
                0
            );
        }
    }

    /**
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}']
        );
    }
}
