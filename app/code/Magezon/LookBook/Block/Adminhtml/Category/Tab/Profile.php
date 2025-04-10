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

namespace Magezon\LookBook\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magezon\LookBook\Model\Category;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;

class Profile extends Extended
{
    /**
     * @var ProfileCollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $coreRegistry,
        ProfileCollectionFactory $profileCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->coreRegistry    = $coreRegistry;
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lookbook_profile_category');
        $this->setDefaultSort('profile_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in profile flag
        if ($column->getId() == 'in_category') {
            $profileIds = $this->_getSelectedProfiles();
            if (empty($profileIds)) {
                $profileIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('profile_id', ['in' => $profileIds]);
            } elseif (!empty($profileIds)) {
                $this->getCollection()->addFieldToFilter('profile_id', ['nin' => $profileIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }

        $collection = $this->profileCollectionFactory->create();
        $this->setCollection($collection);

        if ($this->getCategory()->getProfilesReadonly()) {
            $profileIds = $this->_getSelectedProfiles();
            if (empty($profileIds)) {
                $profileIds = 0;
            }
            $this->getCollection()->addFieldToFilter('profile_id', ['in' => $profileIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getProfilesReadonly()) {
            $this->addColumn(
                'in_category',
                [
                    'type'             => 'checkbox',
                    'name'             => 'in_category',
                    'values'           => $this->_getSelectedProfiles(),
                    'index'            => 'profile_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }

        $this->addColumn(
            'profile_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'profile_id',
                'header_css_class' => 'col-id _fit',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index'  => 'title'
            ]
        );

        $this->addColumn(
            'identifier',
            [
                'header' => __('URL Key'),
                'index'  => 'identifier'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header'   => __('Status'),
                'index'    => 'is_active',
                'type'     => 'options',
                'renderer' => 'Magezon\LookBook\Block\Adminhtml\Profile\Renderer\Status',
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
                'header_css_class' => 'col-id _fit'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'type'      => 'action',
                'edit_only' => true,
                'sortable'  => false,
                'editable'  => false,
                'filter'    => false,
                'style'     => 'width:10px;',
                'renderer'  => 'Magezon\LookBook\Block\Adminhtml\Profile\Renderer\Action',
                'header_css_class' => '_fit'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header'           => __('Position'),
                'type'             => 'number',
                'index'            => 'position',
                'header_css_class' => 'mgz-hidden',
                'column_css_class' => 'mgz-hidden',
                'validate_class'   => 'admin__control-text validate-number',
                'editable'         => 1
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('lookbook/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProfiles()
    {
        $profiles = $this->getRequest()->getPost('selected_profiles');
        if ($profiles === null) {
            $profiles = $this->getCategory()->getProfilesPosition();
            return array_keys($profiles);
        }
        return $profiles;
    }
}
