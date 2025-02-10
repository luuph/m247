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
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction;

use Bss\RewardPoint\Model\TransactionFactory;
use Magento\Customer\Controller\RegistryConstants;
use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Renderer\TransactionType;
use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Renderer\PointBalance;

/**
 * Class grid
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $websitesCollectionFactory;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\Customer $customerRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesCollectionFactory
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\Customer $customerRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesCollectionFactory,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        array $data = []
    ) {
        $this->context                   = $context;
        $this->backendHelper             = $backendHelper;
        $this->customerRepository        = $customerRepository;
        $this->registry                  = $registry;
        $this->websitesCollectionFactory = $websitesCollectionFactory;
        $this->transactionFactory        = $transactionFactory;

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
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Records Found'));
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('bssreward/customer/transaction', ['_current' => true]);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customer = $this->_getCustomer();
        $collection = $this->transactionFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())->addFieldToSelect("*");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare column
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'transaction_id',
            [
                'header' => __('Id'),
                'index' => 'transaction_id',
            ]
        );
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
            'point',
            [
                'header' => __('Changed Point'),
                'index' => 'point',
            ]
        );
        $this->addColumn(
            'point_expired',
            [
                'header' => __('Point Expired'),
                'index' => 'point_expired',
            ]
        );
        $this->addColumn(
            'point_balance',
            [
                'header' => __('Balance'),
                'renderer' => PointBalance::class,
                'index' => 'point_balance',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Transaction Date'),
                'index' => 'created_at',
            ]
        );
        $this->addColumn(
            'note',
            [
                'header' => __('Note'),
                'index' => 'note',
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Transaction Type'),
                'index' => 'action',
                'renderer'  => TransactionType::class,
            ]
        );
        $this->addColumn(
            'created_by',
            [
                'header' => __('Created By'),
                'index' => 'created_by',
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
    protected function _getCustomer()
    {
        if ($customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            $customerData = $this->customerRepository->load($customerId);
            return $customerData;
        }

        return false;
    }
}
