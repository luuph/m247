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

namespace Bss\DynamicCategory\Model\Config\Source;

use Bss\DynamicCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @var RuleCollectionFactory
     */
    protected RuleCollectionFactory $ruleCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        RequestInterface $request
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->request = $request;
    }

    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray($addEmpty = true)
    {
        $storeId = $this->request->getParam('store');
        $dynamicCatIds = [];
        $ruleCollection = $this->ruleCollectionFactory->create();
        foreach ($ruleCollection as $rule) {
            $dynamicCatIds[] = $rule->getRuleId();
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => $dynamicCatIds])
            ->addAttributeToSelect(['name', 'is_active'])
            ->setStoreId($storeId);

        $options = [];

        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }

        return $options;
    }
}
