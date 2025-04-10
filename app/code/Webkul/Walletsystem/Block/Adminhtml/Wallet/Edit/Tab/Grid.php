<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit\Tab;

use \Magento\Customer\Model\CustomerFactory;

/**
 * Webkul Walletsystem Grid Block
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Validator\Constraint\Option\CallbackFactory
     */
    protected $callback;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context                        $context
     * @param \Magento\Backend\Helper\Data                                   $backendHelper
     * @param CustomerFactory                                                $customerFactory
     * @param \Magento\Framework\Validator\Constraint\Option\CallbackFactory $callback
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CustomerFactory $customerFactory,
        \Magento\Framework\Validator\Constraint\Option\CallbackFactory $callback,
        array $data = []
    ) {
        $this->customer = $customerFactory;
        $this->callback = $callback;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('walletcustomergrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    /**
     * Get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'walletsystem/wallet/grid',
            ['_current' => true]
        );
    }
    /**
     * Prepare Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->customer->create()->getCollection();
        $walletTransactionTable = $collection->getTable('wk_ws_wallet_record');
        $collection->getSelect()->joinLeft(
            ['walletrecord'=>$collection->getTable('wk_ws_wallet_record')],
            'e.entity_id = walletrecord.customer_id',
            [
                "total_amount"=> "total_amount",
                "remaining_amount"=>"remaining_amount"
            ]
        );
        $collection->addNameToSelect();

        $collection->addFilterToMap("total_amount", "walletrecord.total_amount");
        $collection->addFilterToMap("remaining_amount", "walletrecord.remaining_amount");

        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }

    /**
     * Prepare Columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_customer',
            [
                'type' => 'checkbox',
                'name' => 'in_customer',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Customer ID'),
                'index' =>  'entity_id',
                'class' =>  'customerid'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Customer Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email ID'),
                'index' =>  'email'
            ]
        );

        $this->addColumn(
            'remaining_amount',
            [
                'header' => __('Remaining Wallet Amount'),
                'index' =>  'remaining_amount',
                'renderer' => \Webkul\Walletsystem\Block\Adminhtml\Wallet\Renderer\PriceFormatter::class,
                'type'  => 'text',
                'gmtoffset'                 => true,
                'sortable' => false,
                'filter_index'  => 'walletrecord.remaining_amount',
                'filter_condition_callback' => [$this, 'filterRemainingAmount'],
                'order_callback'            => [$this, 'sortRemainingAmount']
            ]
        );
        $this->addColumn(
            'total_amount',
            [
                'header' => __('Total Wallet Amount'),
                'index' =>  'total_amount',
                'renderer' => \Webkul\Walletsystem\Block\Adminhtml\Wallet\Renderer\TotalAmountRenderer::class,
                'type'  => 'text',
                'sortable' => false,
                'filter_index'  => 'walletrecord.total_amount',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterTotalAmount'],
                'order_callback'            => [$this, 'sortTotalAmount']
            ]
        );
        $this->addColumn(
            'addamount',
            [
                'header' => __('Adjust Wallet Amount'),
                'index' =>  'addamount',
                'renderer' => \Webkul\Walletsystem\Block\Adminhtml\Wallet\Renderer\Addamountbutton::class,
                'sortable' => false,
                'filter'    =>  false
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Filter remaining amount
     *
     * @param object $collection
     * @param object $column
     */
    public function filterRemainingAmount($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('walletrecord.remaining_amount', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort remining amount
     *
     * @param object $collection
     * @param object $column
     */
    public function sortRemainingAmount($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    /**
     * Filter total amount
     *
     * @param object $collection
     * @param object $column
     */
    public function filterTotalAmount($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('walletrecord.total_amount', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort total amount
     *
     * @param object $collection
     * @param object $column
     */
    public function sortTotalAmount($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
}
