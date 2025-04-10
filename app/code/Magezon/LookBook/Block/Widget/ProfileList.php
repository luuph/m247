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

namespace Magezon\LookBook\Block\Widget;

class ProfileList extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    protected $collection;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\LookBook\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                  $context           
     * @param \Magento\Framework\App\Http\Context                               $httpContext       
     * @param \Magezon\Core\Helper\Data                                         $coreHelper        
     * @param \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory   $collectionFactory 
     * @param \Magezon\LookBook\Helper\Data                                     $dataHelper        
     * @param array                                                             $data              
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        \Magezon\LookBook\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext       = $httpContext;
        $this->coreHelper        = $coreHelper;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper        = $dataHelper;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getData('template')) {
            $this->setTemplate($this->getData('template'));
        } else {
            $this->setTemplate('widget/profile_list.phtml');
        }
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [\Magezon\LookBook\Model\Profile::CACHE_TAG]
        ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheInfo = [
            'LOOKBOOK_PROFILE_WIDGET',
            $this->_storeManager->getStore()->getId(),
            (int)$this->_storeManager->getStore()->isCurrentlySecure(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            $this->coreHelper->serialize($this->getData())
        ];

        return $cacheInfo;
    }
    
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }
        return parent::_toHtml();
    }

    /**
     * @return Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function getCollection()
    {
        if ($this->collection == null) {
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();

            if ($page_size = $this->getData('page_size')) {
                $collection->setPageSize((int)$page_size);
            }
            $categories = $this->getData('categories');
            if ($categories) {
                $categories = explode(",", $categories);
                $collection->getSelect()->joinLeft(
                    ['mlpc' => $collection->getResource()->getTable('mgz_lookbook_profile_category')],
                    'main_table.profile_id = mlpc.profile_id',
                    []
                )->group('main_table.profile_id');
                $collection->getSelect()->where('category_id IN (?)', $categories);
            }
            $profileIds = $this->getData('profile_ids');
            if ($profileIds) {
                $profileIds = explode(",", $profileIds);
                $collection->getSelect()->orWhere('main_table.profile_id IN (?)', $profileIds);
            }
            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getProfileListHtml()
    {
        $collection = $this->getCollection();
        $block = $this->getLayout()->createBlock(\Magezon\LookBook\Block\ListProfile::class);
        $block->setLayoutType('carousel');
        $block->setCollection($collection);
        return $block->toHtml();
    }
}