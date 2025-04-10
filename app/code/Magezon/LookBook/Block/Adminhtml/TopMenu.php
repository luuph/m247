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

namespace Magezon\LookBook\Block\Adminhtml;

class TopMenu extends \Magezon\Core\Block\Adminhtml\TopMenu
{
    /**
     * @return array
     */
    public function intLinks()
    {
        $links = [
            [
                [
                    'title'    => __('Add New Profile'),
                    'link'     => $this->getUrl('lookbook/profile/new'),
                    'resource' => 'Magezon_LookBook::profile_save'
                ],
                [
                    'title'    => __('Manage Profiles'),
                    'link'     => $this->getUrl('lookbook/profile'),
                    'resource' => 'Magezon_LookBook::profile'
                ],
                [
                    'title'    => __('Add New Category'),
                    'link'     => $this->getUrl('lookbook/category/new'),
                    'resource' => 'Magezon_LookBook::category_save'
                ],
                [
                    'title'    => __('Manage Categories'),
                    'link'     => $this->getUrl('lookbook/category'),
                    'resource' => 'Magezon_LookBook::category'
                ],
                [
                    'class' => 'separator'
                ],
                [
                    'title'    => __('Settings'),
                    'link'     => $this->getUrl('admin/system_config/edit/section/mgzlookbook'),
                    'resource' => 'Magezon_LookBook::settings'
                ],
                [
                    'title'    => __('Cache Management'),
                    'link'     => $this->getUrl('adminhtml/cache/'),
                    'target' => '_blank'
                ]
            ]
        ];
        return $links;
    }
}
