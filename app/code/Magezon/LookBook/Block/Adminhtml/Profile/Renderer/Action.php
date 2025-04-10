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

namespace Magezon\LookBook\Block\Adminhtml\Profile\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Url;

class Action extends Text
{
    /**
     * @param Magento\Framework\DataObject $row
     * @return string
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $editUrl = $this->getUrl(
            'lookbook/profile/edit',
            [
                'profile_id' => $row['profile_id']
            ]
        );
        return sprintf("<a target='_blank' href='%s'>" . __('Edit') . "</a>", $editUrl);
    }
}
