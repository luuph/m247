<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Block\Adminhtml\Profile\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magezon\LookBook\Block\Adminhtml\Profile\Edit\Button\Generic;

class Apply extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->_isAllowedAction('Magezon_LookBook::profile_save')) {
            $data = [
                'id_hard'        => 'save_apply',
                'label'          => __('Save and Apply'),
                'on_click'       => '',
                'data_attribute' => $this->getButtonAttribute([ true, ['auto_apply' => 1]])
            ];
        }
        return $data;
    }
    
    /**
     * @param  array $params
     * @return array
     */
    public function getButtonAttribute($params = [])
    {
        $attributes = [
            'mage-init' => [
                'Magento_Ui/js/form/button-adapter' => [
                    'actions' => [
                        [
                            'targetName' => 'lookbook_profile_form.lookbook_profile_form',
                            'actionName' => 'save',
                            'params'     => $params
                        ]
                    ]
                ]
            ]
        ];
        return $attributes;
    }
}
