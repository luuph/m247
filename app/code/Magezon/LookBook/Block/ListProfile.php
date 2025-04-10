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

namespace Magezon\LookBook\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;

class ListProfile extends Template
{
    const DEFAULT_PROFILES_COUNT    = 4;
    const DEFAULT_PROFILES_PER_PAGE = 8;
    const DEFAULT_SHOW_PAGER        = false;
    const PAGE_VAR_NAME             = 'np';

    /**
     * @var string
     */
    protected $_template = 'Magezon_LookBook::list.phtml';

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Data
     */
    protected $dataHelper; 

    /**
     * @var integer
     */
    protected $pageSize;

    /**
     * @var Pager
     */
    protected $pager;
    
    /**
     * @var string
     */
    protected $profilesHtml;

    /**
     * ListProfile constructor.
     * @param Template\Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        $collection = $this->collection;
        if ($this->showPager()) {
            $collection->setPageSize($this->getPageSize())
                ->setCurPage($this->getRequest()->getParam($this->getPageVarName(), 1));
        }
        return $collection;
    }

    /**
     * @param Collection $collection
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Retrieve how many profiles should be displayed
     *
     * @return int
     */
    public function getProfilesCount()
    {
        if (null === $this->getData('profiles_count')) {
            $this->setData('profiles_count', self::DEFAULT_PROFILES_COUNT);
        }

        return $this->getData('profiles_count');
    }

    /**
     * Retrieve how many posts should be displayed
     *
     * @return int
     */
    public function getProfilesPerPage()
    {
        if (!$this->hasData('profiles_per_page')) {
            $this->setData('profiles_per_page', self::DEFAULT_PROFILES_PER_PAGE);
        }
        return $this->getData('profiles_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function getPageVarName()
    {
        if (!$this->hasData('page_var_name')) {
            $this->setData('page_var_name', self::PAGE_VAR_NAME);
        }
        return $this->getData('page_var_name');
    }

    /**
     * Retrieve how many posts should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        if ($this->pageSize !== null) {
            return $this->pageSize;
        }
        return $this->showPager() ? $this->getProfilesPerPage() : $this->getProfilesCount();
    }

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $size = $this->getCollection()->getSize();
        if ($this->showPager() && $size > $this->getProfilesPerPage() && $this->getProfilesPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    Pager::class
                );
                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getPageVarName())
                    ->setLimit($this->getProfilesPerPage())
                    ->setTotalLimit($this->getProfilesCount())
                    ->setCollection($this->getCollection());
            }
            if ($this->pager instanceof AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getProfileHtml($profile)
    {
        $block = $this->getLayout()->createBlock(\Magezon\LookBook\Block\Profile::class);
        $block->setProfile($profile);
        $block->setLayoutType('carousel');
        return $block->toHtml(); 
    }

    public function getNoResultText() {
        if ($this->hasData('no_result_text')) {
            return $this->getData('no_result_text');
        }
        return __('We can\'t find profiles matching the selection.');
    }
}