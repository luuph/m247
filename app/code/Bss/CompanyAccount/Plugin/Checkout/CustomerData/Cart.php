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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Checkout\CustomerData;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class Cart
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepository;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @param Data $helper
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Data    $helper,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $session
    ) {
        $this->helper = $helper;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
    }

    /**
     * Set status sub-quote
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetSectionData($subject, array $result): array
    {
        if (!$this->helper->isEnable()) {
            return $this->setCheckoutQuoteFlag($result, false);
        }

        $currentQuoteId = $this->checkoutSession->getQuote()->getId();
        $subQuote = $this->subQuoteRepository->getByQuoteId($currentQuoteId);

        $isCheckoutQuote = $subQuote &&
            in_array(
                $subQuote->getQuoteStatus(),
                [Data::SUB_QUOTE_APPROVED, Data::SUB_QUOTE_WAITING],
                true
            );

        return $this->setCheckoutQuoteFlag($result, $isCheckoutQuote);
    }

    /**
     * Helper function to set 'isCheckoutQuote' flag for items
     *
     * @param array $result
     * @param bool $flag
     * @return array
     */
    private function setCheckoutQuoteFlag(array $result, bool $flag): array
    {
        if (!isset($result['items'])) {
            return $result;
        }

        foreach ($result['items'] as &$item) {
            $item['isCheckoutQuote'] = $flag;
        }

        return $result;
    }
}
