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

namespace Magezon\LookBook\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magezon\LookBook\Block\ListProfile;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory;
use Magezon\LookBook\Model\Profile;

class Profiles extends Template
{
    /**
     * @var string
     */
    protected $_template = "Magezon_LookBook::sidebar/profiles.phtml";

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * SortProfiles constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->getCollection()->count()) {
            return parent::toHtml();
        }
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $pageSize   = $this->dataHelper->getSidebarNumberOfProfile();
            $sortProfile  = $this->dataHelper->getSidebarProfileSort();
            switch ($sortProfile) {
                case Profile::SORT_TIME_ASC:
                    $sortField = 'creation_time';
                    $sortType = 'ASC';
                    break;
                case Profile::SORT_TIME_DESC:
                    $sortField = 'creation_time';
                    $sortType = 'DESC';
                    break;
                case Profile::SORT_NAME_ASC:
                    $sortField = 'title';
                    $sortType = 'ASC';
                    break;
                case Profile::SORT_NAME_DESC:
                    $sortField = 'title';
                    $sortType = 'DESC';
                    break;
            }
            $this->collection = $this->collectionFactory->create();
            $this->collection->addIsActiveFilter();
            $this->collection->prepareCollection()
                ->setOrder($sortField, $sortType)
                ->setPageSize($pageSize);
        }
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getProfileListHtml()
    {
        $collection = $this->getCollection();
        $block = $this->getLayout()->createBlock(ListProfile::class);
        $block->setCollection($collection);
        return $block->toHtml();
    }
}