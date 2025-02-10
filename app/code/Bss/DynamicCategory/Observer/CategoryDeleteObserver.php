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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CategoryDeleteObserver implements ObserverInterface
{
    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param RuleRepository $ruleRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        RuleRepository $ruleRepository,
        LoggerInterface $logger
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
    }

    /**
     * Handler for category delete event
     *
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getData('category');
        try {
            $this->ruleRepository->deleteById($category->getId());
        } catch (\Exception $e) {
            $this->logger->notice($e->getMessage());
        }
    }
}
