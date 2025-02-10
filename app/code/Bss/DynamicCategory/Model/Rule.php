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

namespace Bss\DynamicCategory\Model;

use Bss\DynamicCategory\Api\Data\RuleInterface;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\CollectionFactory as ActionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DataObject;

/**
 * Rule model
 *
 * @method Rule setCategory($category)
 * @method getCategory()
 * @method Rule setCollectedAttributes($attributes)
 * @method getCollectedAttributes()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends AbstractModel implements RuleInterface
{
    /**
     * Constants cache tag
     */
    public const CACHE_TAG = 'BSS_DYNAMIC_CATEGORY_RULE';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'bss_dynamic_category_rule';

    /**
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * @var CombineFactory
     */
    protected $combineFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * Limitation for products collection
     *
     * @var int|int[]|null
     */
    protected $productsFilter;

    /**
     * Visibility filter flag
     *
     * @var bool
     */
    protected $visibilityFilter = true;

    /**
     * Iterator resource
     *
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * Product model factory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param ActionFactory $actionFactory
     * @param Iterator $resourceIterator
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param RequestInterface $request
     * @param Session $backendSession
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        CombineFactory $combineFactory,
        ActionFactory $actionFactory,
        Iterator $resourceIterator,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        RequestInterface $request,
        Session $backendSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionFactory = $actionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->request = $request;
        $this->backendSession = $backendSession;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Faq initialize with param is ResourceModel to get data from db
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Bss\DynamicCategory\Model\ResourceModel\Rule::class);
    }

    /**
     * Get rule id
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Set rule condition
     *
     * @param int $id
     * @return RuleInterface|Rule
     */
    public function setRuleId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    /**
     * Get rule condition
     *
     * @return string
     */
    public function getRuleCondition()
    {
        return $this->getData(self::RULE_CONDITION);
    }

    /**
     * Set rule condition
     *
     * @param string $ruleCondition
     * @return RuleInterface|Rule
     */
    public function setRuleCondition($ruleCondition)
    {
        return $this->setData(self::RULE_CONDITION, $ruleCondition);
    }

    /**
     * Get condition instance
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Get rule action instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionFactory->create();
    }

    /**
     * Retrieve condition field set id
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getRuleId();
    }

    /**
     * Retrieve array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $data = $this->request->getPost('rule');

            if ($data) {
                $this->backendSession->setRuleDataPost(['rule' => $data]);
            } else {
            	$rulePost = $this->backendSession->getRuleDataPost();
                if ($rulePost) {
                    $data = $rulePost['rule'];
                }
            }
            if (!$data) {
                $data = [];
            }
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            $productCollection = $this->productCollectionFactory->create();

            if ($this->productsFilter) {
                $productCollection->addIdFilter($this->productsFilter);
            }
            $this->filterVisibilityCollection($productCollection, false);
            $this->loadPost($data);
            if (!$this->getCategory()) {
                $this->setRuleCondition($this->serializer->serialize($this->getConditions()->asArray()));
            }
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->productFactory->create()
                ]
            );
        }
        return $this->productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
            if ($results[$websiteId] === true) {
                $this->productIds[$product->getId()] = 1;
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param  int|int[] $productIds
     * @return void
     */
    public function setProductsFilter($productIds)
    {
        $this->productsFilter = $productIds;
    }

    /**
     * Retrieve products filter
     *
     * @return int|int[]|null
     */
    public function getProductsFilter()
    {
        return $this->productsFilter;
    }

    /**
     * Toggle visibility filter
     *
     * @param  bool $enabled
     * @return void
     */
    public function setVisibilityFilter($enabled)
    {
        $this->visibilityFilter = $enabled;
    }

    /**
     * Check VisibilityFilter should be enabled
     *
     * @return bool
     */
    public function isVisibilityFilter()
    {
        return $this->visibilityFilter;
    }

    /**
     * Retrieve unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getRuleId()];
    }

    /**
     * Filter collection with visibility of product
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $visibility
     * @return void
     */
    public function filterVisibilityCollection($collection, $visibility)
    {
        $this->setVisibilityFilter($visibility);
        if ($this->visibilityFilter) {
            $collection->addAttributeToFilter(
                'visibility',
                ['in' => $this->catalogProductVisibility->getVisibleInSiteIds()]
            );
        }
    }
}
