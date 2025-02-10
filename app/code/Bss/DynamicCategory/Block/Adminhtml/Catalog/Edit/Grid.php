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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Block\Adminhtml\Catalog\Edit;

use Bss\DynamicCategory\Model\RuleFactory;
use Bss\DynamicCategory\Model\RuleRepository;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Exception;
use Magento\Framework\Exception\FileSystemException;

class Grid extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Visibility
     */
    protected $visibility;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $productCollectionFactory
     * @param RuleFactory $ruleFactory
     * @param Visibility $visibility
     * @param Status $status
     * @param RuleRepository $ruleRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $productCollectionFactory,
        RuleFactory $ruleFactory,
        Visibility $visibility,
        Status $status,
        RuleRepository $ruleRepository,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->visibility = $visibility;
        $this->status = $status;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create()->addStoreFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('status');
        $productList = $this->getSelectedProducts();
        if (count($productList)) {
            $collection->addIdFilter(array_keys($productList));
        } else {
            $collection->addIdFilter([0]);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header' => __('Product ID'),
            'type' => 'number',
            'index' => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index' => 'name',
            'width' => '50px',
        ]);
        $this->addColumn('sku', [
            'header' => __('SKU'),
            'index' => 'sku',
            'width' => '50px',
        ]);
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'source' => Status::class,
                'options' => $this->status->getOptionArray()
            ]
        );
        $this->addColumn('price', [
            'header' => __('Price'),
            'type' => 'currency',
            'index' => 'price',
            'width' => '50px',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Get grid url for product preview
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('dynamic_category/rule/productpreview', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl(
            'catalog/product/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $item->getId()]
        );
    }

    /**
     * Get selected products
     *
     * @return array|false
     */
    protected function getSelectedProducts()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        try {
            $rule = $this->ruleRepository->get($ruleId);
        } catch (\Exception $e) {
            $rule = $this->ruleFactory->create();
        }
        if (!$rule) {
            return false;
        }

        return $rule->getMatchingProductIds();
    }

    /**
     * Retrieve status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Retrieve status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return true;
    }
}
