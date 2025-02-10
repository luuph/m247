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
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Bss\RewardPoint\Model\TransactionFactory;
use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Renderer\PointsMaximum;
use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Renderer\PointsThreshold;

/**
 * Class grid
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Data
     */
    protected $backendHelper;

    /**
     * @var Customer
     */
    protected $customerRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $websitesCollectionFactory;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Customer $customerRepository
     * @param Registry $registry
     * @param CollectionFactory $websitesCollectionFactory
     * @param TransactionFactory $transactionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Customer $customerRepository,
        Registry $registry,
        CollectionFactory $websitesCollectionFactory,
        TransactionFactory $transactionFactory,
        array $data = []
    ) {
        $this->context = $context;
        $this->backendHelper = $backendHelper;
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
        $this->websitesCollectionFactory = $websitesCollectionFactory;
        $this->transactionFactory = $transactionFactory;
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
        $this->setId('balanceGrid');
        $this->setDefaultSort('website_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Records Found'));
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $customer = $this->getCustomer();
        $collection = $this->transactionFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $customer->getId());
        $collection->_calculateBalance();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare column
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'website_id',
            [
                'header' => __('Website'),
                'index' => 'website_id',
                'type' => 'options',
                'options' => $this->websitesCollectionFactory->create()->toOptionHash()
            ]
        );
        $this->addColumn(
            'point_balance',
            [
                'header' => __('Points balance'),
                'index' => 'point_balance',
            ]
        );
        $this->addColumn(
            'point_earned',
            [
                'header' => __('Points earned'),
                'index' => 'point_earned',
            ]
        );
        $this->addColumn(
            'point_spent',
            [
                'header' => __('Points spent'),
                'index' => 'point_spent',
            ]
        );
        $this->addColumn(
            'point_maximum',
            [
                'header' => __('Points maximum'),
                'index' => 'website_id',
                'renderer'  => PointsMaximum::class,
            ]
        );
        $this->addColumn(
            'point_threshold',
            [
                'header' => __('Points threshold'),
                'index' => 'website_id',
                'renderer'  => PointsThreshold::class,
            ]
        );
        $this->setFilterVisibility(false);
        $this->setSortable(false);
        return parent::_prepareColumns();
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer|bool
     */
    protected function getCustomer()
    {
        if ($customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            $customerData = $this->customerRepository->load($customerId);

            return $customerData;
        }

        return false;
    }
}
