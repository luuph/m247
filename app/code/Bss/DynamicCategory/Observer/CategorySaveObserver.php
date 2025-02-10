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

use Bss\DynamicCategory\Model\RuleRepository;
use Bss\DynamicCategory\Model\Config as DynamicCategoryConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CategorySaveObserver implements ObserverInterface
{
    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var DynamicCategoryConfig
     */
    protected $dynamicCategoryConfig;

    /**
     * Constructor
     *
     * @param RuleRepository $ruleRepository
     * @param DynamicCategoryConfig $dynamicCategoryConfig
     */
    public function __construct(RuleRepository $ruleRepository, DynamicCategoryConfig $dynamicCategoryConfig)
    {
        $this->ruleRepository = $ruleRepository;
        $this->dynamicCategoryConfig = $dynamicCategoryConfig;
    }

    /**
     * Handler for category save event
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getData('category');
        if ($category->getIsDynamicCategory() && $this->dynamicCategoryConfig->isEnable()) {
            if ($category->getDynamicCategoryRuleError()) {
                throw new LocalizedException(
                    $category->getDynamicCategoryRuleError()
                );
            }
            $rule = $category->getDynamicCategoryRule();
            if ($rule) {
                $rule->setId($category->getId());
                $this->ruleRepository->save($rule);
            }
        }
    }
}
