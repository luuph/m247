<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block;

use Magento\Quote\Model\QuoteFactory;

/**
 * Webkul Walletsystem Class
 */
class Checkout extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletHelper = $walletHelper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Get url on which ajax has been sent for setting wallet amount
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->walletHelper->getAjaxUrl();
    }

    /**
     * Get grandtotal from quote data
     */
    public function getGrandTotal()
    {
        $quote = '';
        if ($this->checkoutSession) {
            if ($this->checkoutSession->getQuoteId()) {
                $quoteId = $this->checkoutSession->getQuoteId();
                $quote = $this->quoteFactory->create()
                    ->load($quoteId);
            }
        }
        if ($quote) {
            $quoteData = $quote->getData();
            if (is_array($quoteData)) {
                if (array_key_exists('grand_total', $quoteData)) {
                    return $grandTotal = $quoteData['grand_total'];
                } else {
                    return 0;
                }
            }
        }
    }
}
