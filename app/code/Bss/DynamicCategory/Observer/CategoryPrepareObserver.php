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

namespace Bss\DynamicCategory\Observer;

use Bss\DynamicCategory\Model\Rule;
use Bss\DynamicCategory\Model\RuleFactory;
use Bss\DynamicCategory\Model\Config as DynamicCategoryConfig;
use Bss\DynamicCategory\Model\RuleRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;
use Exception;
use Magento\Indexer\Model\Indexer\DependencyDecorator;
use Magento\Framework\Serialize\Serializer\Json;

class CategoryPrepareObserver implements ObserverInterface
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var DynamicCategoryConfig
     */
    protected $dynamicCategoryConfig;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var DependencyDecorator
     */
    protected $indexer;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param RuleFactory $ruleFactory
     * @param DynamicCategoryConfig $dynamicCategoryConfig
     * @param RuleRepository $ruleRepository
     * @param Json|null $serializer
     */
    public function __construct(
        RuleFactory $ruleFactory,
        DynamicCategoryConfig $dynamicCategoryConfig,
        RuleRepository $ruleRepository,
        DependencyDecorator $indexer,
        Json $serializer = null
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->dynamicCategoryConfig = $dynamicCategoryConfig;
        $this->ruleRepository = $ruleRepository;
        $this->indexer = $indexer;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
    }

    /**
     * Handler for category prepare event
     *
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getData('request');
        $category = $observer->getEvent()->getData('category');
        $data = $request->getPostValue();

        $rule = $this->ruleFactory->create();
        if ($category->getId()) {
            try {
                $rule = $this->ruleRepository->get($category->getId());
            } catch (\Exception $e) {
                $rule = $this->ruleFactory->create();
            }
        }
        if ($data && $category->getIsDynamicCategory() && $this->dynamicCategoryConfig->isEnable()) {
            if (!isset($data['rule'])) {
                return;
            }

            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);

            $validateResult = $rule->validateData(new DataObject($data));
            if ($validateResult !== true) {
                $category->setDynamicCategoryRuleError($validateResult);
                return;
            }
            $isConditionChanged = $this->checkConditionsChanged($rule, $data['conditions']);
            $rule->loadPost(['conditions' => $data['conditions']]);
            $rule->setCategory($category);
            if ($this->indexer->load(\Bss\DynamicCategory\Model\Indexer\IndexBuilder::INDEXER_ID)->isScheduled()) {
                // update products if changing conditions
                if($isConditionChanged) {
                    $postedProducts = [];
                    $category->setPostedProducts($postedProducts);
                    $category->setDynamicCategoryRule($rule);
                }
            } else {
                // apply rule
                $matchingProducts = $rule->getMatchingProductIds();
                // update position
                $postedProducts = array_intersect_key($category->getPostedProducts() ?: [], $matchingProducts);
                $postedProducts = array_replace($matchingProducts, $postedProducts);

                $category->setPostedProducts($postedProducts);
                $category->setDynamicCategoryRule($rule);
                return;
            }
        }
    }

    /**
     * Check if conditions rule changing
     *
     * @param Rule $rule
     * @param array|null $conditions
     * @return bool
     */
    protected function checkConditionsChanged($rule, $conditions)
    {
        $oldConditions = $rule->getRuleCondition();
        $postRule = $this->ruleFactory->create();
        $postRule->loadPost(['conditions' => $conditions]);
        $newConditions = $this->serializer->serialize($postRule->getConditions()->asArray());
        return ($oldConditions != $newConditions);
    }
}
