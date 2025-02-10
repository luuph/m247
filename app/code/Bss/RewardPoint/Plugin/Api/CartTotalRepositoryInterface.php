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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Api;

/**
 * Class Cart Total Repository Interface
 *
 * Bss\RewardPoint\Plugin\Api
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CartTotalRepositoryInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * CartTotalRepositoryInterface constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->session = $session;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Set Extension Attribute
     *
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $entity
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Quote\Api\CartTotalRepositoryInterface $subject,
        \Magento\Quote\Api\Data\TotalsInterface $entity,
        $quoteId
    ) {
        if ((int)$quoteId) {
            $quote = $this->quoteFactory->create()->load($quoteId, 'entity_id');
            $extensionAttributes = $entity->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setBaseRwpAmount($quote->getBaseRwpAmount());
                $extensionAttributes->setEarnPoints($quote->getEarnPoints());
                $extensionAttributes->setRwpNote($quote->getRwpNote());
                $extensionAttributes->setSpendPoints($quote->getSpendPoints());
                $extensionAttributes->setRwpAmount($quote->getRwpAmount());
                $entity->setExtensionAttributes($extensionAttributes);
            }
        }
        return $entity;
    }
}
