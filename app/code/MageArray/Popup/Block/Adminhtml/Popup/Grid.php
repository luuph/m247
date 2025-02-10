<?php
namespace MageArray\Popup\Block\Adminhtml\Popup;

/**
 * Class Grid
 * @package MageArray\Popup\Block\Adminhtml\Popup
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var
     */
    protected $_popupFactory;
    /**
     * @var \MageArray\Popup\Model\Status
     */
    protected $_status;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MageArray\Popup\Model\Status $status
     * @param \MageArray\Popup\Model\PopupFactory $popupFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MageArray\Popup\Model\Status $status,
        \MageArray\Popup\Model\PopupFactory $popupFactory
    ) {
        $this->_popupFactory = $popupFactory;
        $this->_status = $status;
        parent::__construct($context, $backendHelper);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('popupGrid');
        $this->setDefaultSort('popup_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = $this->_popupFactory->create()->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'popup_id',
            [
                'header' => __('Popup ID'),
                'type' => 'number',
                'index' => 'popup_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'width' => '50px',
                'type' => 'options',
                'options' => ['1' => 'Enabled', '2' => 'Disabled']
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'is_active',
                'type' => 'action',
                'getter' => 'getId',
                'width' => '20px',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                        ],
                        'field' => 'popup_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('popup_id');
        $this->getMassactionBlock()->setFormFieldName('popup');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete', ['' => '']),
            'confirm' => __('Are you sure?')
        ]);
        $statuses = $this->_status->getOptionArray();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem('status', [
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $statuses
                ]
            ]
        ]);

        return $this;
    }
     /**
      * @return string
      */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['popup_id' => $row->getId()]);
    }
}
