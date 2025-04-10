<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Adminhtml\Account\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Mageplaza\Affiliate\Model\Transaction\Status;
use Mageplaza\Affiliate\Model\Transaction\Type;
use Mageplaza\Affiliate\Model\TransactionFactory;

/**
 * Class Logs
 * Mageplaza\Affiliate\Block\Adminhtml\Rule\Edit\Tab\Products
 */
class Logs extends Extended implements TabInterface
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var TransactionFactory
     */
    protected $transaction;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var Status
     */
    public $status;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $coreRegistry
     * @param HelperData $helper
     * @param TransactionFactory $transaction
     * @param Type $type
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $coreRegistry,
        HelperData $helper,
        TransactionFactory $transaction,
        Type $type,
        Status $status,
        array $data = []
    ) {
        $this->helper             = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->transaction              = $transaction;
        $this->type = $type;
        $this->status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('account_logs_gird');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);

    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $logs =$this->getLogs();
        $collection = $this->transaction->create()->getCollection()
            ->addFieldToSelect('transaction_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('type')
            ->addFieldToSelect('amount')
            ->addFieldToSelect('status')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('created_at')
            ->addFieldToFilter('customer_id',$logs->getCustomerId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', [
            'header'           => __('Id'),
            'type'             => 'number',
            'index'            => 'transaction_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'sortable'         => true
        ]);
        $this->addColumn('title', [
            'header'   => __('Title'),
            'index'    => 'title',
            'type'     => 'text',
        ]);
        $this->addColumn('type', [
            'header'   => __('Action Type'),
            'index'    => 'type',
            'type'     => 'options',
            'options' => $this->type->toOptionArrayGird()

        ]);
        $this->addColumn('amount', [
            'header'   => __('Amount'),
            'column_css_class' => 'price',
            'type'             => 'currency',
            'currency_code'    => $this->_storeManager->getStore()->getBaseCurrencyCode(),
            'index'            => 'amount',
            'renderer'         => Price::class
        ]);
        $this->addColumn('status', [
            'header'           => __('Status'),
            'index'            => 'status',
            'type'     => 'options',
            'options'  => $this->status->toOptionArrayGird(),
        ]);
        $this->addColumn('store_id', [
            'header'   => __('Store '),
            'type'     => 'store',
            'index'    => 'store_id',
            'store_view' => true
        ]);
        $this->addColumn('created_at', [
            'header'   => __('Create at'),
            'type'     => 'datetime',
            'index'    => 'created_at',
        ]);
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => 'affiliate/transaction/view',
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => true,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );


        return parent::_prepareColumns();
    }


    /**
     * @return mixed|null
     */
    public function getLogs()
    {
        return $this->coreRegistry->registry('current_account');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Transaction Logs');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('affiliate/transaction/logs', ['_current' => true]);
    }
    /**
     * get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/logs', ['id' => $this->getLogs()->getCustomerId()]);
    }

}
