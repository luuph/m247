<?php

namespace Appristine\LocalShipping\Block\Adminhtml\Carrier\LocalShipping;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_websiteId;

    protected $_tablerate;

    protected $_collectionFactory;
    
    protected $_conditionName;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Appristine\LocalShipping\Model\ResourceModel\LocalShipping\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param  int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Prepare shipping table rate collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
  

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'city',
            ['header' => __('City'), 'index' => 'city', 'default' => '']
        );
        $this->addColumn(
            'zipcode',
            ['header' => __('Zipcode'), 'index' => 'zipcode', 'default' => '']
        );  
        return parent::_prepareColumns();
    }
}
