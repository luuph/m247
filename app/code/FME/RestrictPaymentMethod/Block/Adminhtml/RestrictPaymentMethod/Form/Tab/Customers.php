<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Block\Adminhtml\RestrictPaymentMethod\Form\Tab;

use Magento\Customer\Controller\RegistryConstants;

class Customers extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_customerFactory;

    protected $_privatesalesFactory;
    protected $_paymentmethodFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Cms\Model\BlockFactory $cmsBlockFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $coreRegistry,
        \FME\RestrictPaymentMethod\Model\PaymentMethod $paymentModel,
        array $data = []
    ) {

        $this->_customerFactory = $customerFactory;
        $this->_paymentmethodFactory = $paymentModel;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('customer_list');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(['in_reps' => 1]);
        }
        // if ($this->isReadonly()) {
        //     $this->setFilterVisibility(false);
        // }
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_reps') {
            $blockIds = $this->_getSelectedCustomers();
            if (empty($blockIds)) {
                $blockIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $blockIds]);
            } else {
                if ($blockIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $blockIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_customerFactory->create()
                ->getCollection();
        if ($this->isReadonly()) {
            $customerIds = $this->_getSelectedCustomers();
            if (empty($customerIds)) {
                $customerIds = [0];
            }
            $collection->addFieldToFilter('entity_id', ['in' => $customerIds]);
        }
        $collection->joinField(
            'customer_group_code',
            'customer_group',
            'customer_group_code',
            'customer_group_id=group_id'
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getCustomers()
    {
        return $this->_coreRegistry->registry('fme_paymentmethod');
    }

    public function isReadonly()
    {
        return 0;
    }

    protected function _prepareColumns()
    {

            $this->addColumn(
                'in_reps',
                [
                'type' => 'checkbox',
                'name' => 'in_reps',
                'values' => $this->_getSelectedCustomers(),
                'header_css_class' => 'a-center',
                'index' => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
                'use_index' => true
                ]
            );
        
        $this->addColumn(
            'entity_id',
            [
            'header' => __('ID'),
            'sortable' => true,
            'index' => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'firstname',
            [
            'header' => __('First Name'),
            'index' => 'firstname',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn(
            'lastname',
            [
            'header' => __('Last Name'),
            'index' => 'lastname',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn(
            'customer_group_code',
            [
            'header' => __('Group'),
            'index' => 'customer_group_code',
            'header_css_class' => 'col-group',
            'column_css_class' => 'col-group'
            ]
        );
        $this->addColumn(
            'email',
            [
            'header' => __('Email'),
            'index' => 'email',
            'header_css_class' => 'col-email',
            'column_css_class' => 'col-email'
            ]
        );


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('paymentmethod/customers/grid', ['_current' => true]);
    }

    public function _getSelectedCustomers()
    {
        try {
            $blocks = $this->getRelatedCustomers();
            if (!is_array($blocks)) {
                $blocks = array_keys($this->getCustomers());
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $blocks;
    }

    public function getCustomers()
    {
        $id = $this->getRequest()->getParam('id');
        $blocksArr = [];
        foreach ($this->_paymentmethodFactory->getRelatedCustomers($id) as $blocks) {
            $blocksArr[$blocks['entity_id']] = ['position' => '0'];
        }
        return $blocksArr;
    }
}
