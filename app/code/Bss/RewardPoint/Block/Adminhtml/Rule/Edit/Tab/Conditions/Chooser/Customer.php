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
namespace Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab\Conditions\Chooser;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\System\Store;

/**
 * Class customer
 *
 * Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab\Conditions\Chooser
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Collection
     */
    protected $customerGroup;

    /**
     * @var CollectionFactory
     */
    protected $customerCollection;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * Customer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        $this->customerCollection = $customerCollection;
        $this->systemStore = $systemStore;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('customerChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Add column filter to collection
     *
     * @param Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_customers') {
            $selected = $this->getSelectedCustomers();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('email', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('email', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerCollection->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_customers',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_customers',
                'values' => $this->getSelectedCustomers(),
                'align' => 'center',
                'index' => 'email',
                'use_index' => true
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id'
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'name' => 'firstname',
                'index' => 'firstname'
            ]
        );
        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'name' => 'lastname',
                'index' => 'lastname'
            ]
        );
        $this->addColumn(
            'chooser_email',
            [
                'header' => __('Email'),
                'name' => 'chooser_email',
                'index' => 'email'
            ]
        );

        $customerGroups = $this->customerGroup->addFieldToFilter(
            'customer_group_id',
            ['gt'=> 0]
        )->load()->toOptionHash();

        $this->addColumn(
            'group_id',
            [
                'header' => __('Group'),
                'index' => 'group_id',
                'type' => 'options',
                'options' => $customerGroups
            ]
        );
        $this->addColumn(
            'website_id',
            [
                'header' => __('Web site'),
                'index' => 'website_id',
                'type' => 'options',
                'options' => $this->systemStore->getWebsiteOptionHash()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/chooser',
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }

    /**
     * Get selected customer
     *
     * @return mixed
     */
    protected function getSelectedCustomers()
    {
        $customers = $this->getRequest()->getPost('selected', []);

        return $customers;
    }
}
