<?php
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
    public function afterGetSectionData($subject, $result)
    {
        if (!$this->helper->isEnable()) {
            return $result;
        }
        $currentQuote = $this->checkoutSession->getQuote();
        if (isset($result['items'])) {
            $currentQuoteId = $currentQuote->getId();
            $subQuote = $this->subQuoteRepository->getByQuoteId($currentQuoteId);
            $items = $result['items'];
            foreach ($items as &$item) {
                if ($subQuote && ($subQuote->getQuoteStatus() == Data::SUB_QUOTE_APPROVED
                        || $subQuote->getQuoteStatus() == Data::SUB_QUOTE_WAITING)) {
                    $item['isCheckoutQuote'] = true;
                } else {
                    $item['isCheckoutQuote'] = false;
                }
            }
            $result['items'] = $items;
        }
        return $result;
    }
}
