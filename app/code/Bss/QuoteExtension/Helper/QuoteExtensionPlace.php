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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\ManageQuoteFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Class QuoteExtensionPlace
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteExtensionPlace extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var
     */
    protected $idQuoteExtensionPlace;

    /**
     * @var ManageQuoteFactory
     */
    protected $manageQuote;

    /**
     * Model constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param RequestInterface $request
     * @param ManageQuoteFactory $manageQuote
     * @param Context $context
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        RequestInterface $request,
        ManageQuoteFactory $manageQuote,
        Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->manageQuote = $manageQuote;
        parent::__construct($context);
    }

    /**
     * Check quote extension place
     *
     * @return string
     */
    public function checkQuoteExtensionPlace()
    {
        $urlCurrent = "";
        $httpReferer = $this->request->getServer('HTTP_REFERER');
        $redirectUrl = $this->request->getServer('REDIRECT_URL');
        $request_uri = $this->request->getServer('REQUEST_URI');
        if ($request_uri && strpos($request_uri, 'quoteextension/quote/viewSubmit') !== false) {
            return "";
        }
        if ($httpReferer && strpos($httpReferer, 'quoteextension/index/index') !== false) {
            $urlCurrent = $httpReferer;
        } elseif ($redirectUrl && strpos($redirectUrl, 'quoteextension/index/index') !== false) {
            $urlCurrent = $redirectUrl;
        } elseif ($request_uri && strpos($request_uri, 'quoteextension/index/index') !== false) {
            $urlCurrent = $request_uri;
        } elseif ($httpReferer && strpos($httpReferer, 'quoteextension/quote/view') !== false) {
            $urlCurrent = $httpReferer;
        } elseif ($redirectUrl && strpos($redirectUrl, 'quoteextension/quote/view') !== false) {
            $urlCurrent = $redirectUrl;
        } elseif ($request_uri && strpos($request_uri, 'quoteextension/quote/view') !== false) {
            $urlCurrent = $request_uri;
        }
        if ($redirectUrl && strpos($redirectUrl, 'customer/section/load') !== false) {
            return "";
        }
        return $urlCurrent;
    }

    /**
     * Get id quote when place order quote extension
     *
     * @return null|int
     */
    public function getIdQuoteExtensionPlace()
    {
        $urlCurrent = $this->checkQuoteExtensionPlace();
        if ($urlCurrent) {
            if (!$this->idQuoteExtensionPlace) {
                $quoteExtensionId = null;
                if (str_contains($urlCurrent, 'quoteextension/index/index')) {
                    $positionStartQuoteIdExtension = strpos(
                            $urlCurrent,
                            '/quote/',
                            strpos($urlCurrent, 'quoteextension/index/index')
                        ) + strlen("/quote/");
                    $positionEndQuoteIdExtension = strpos(
                        $urlCurrent,
                        '/',
                        $positionStartQuoteIdExtension
                    );
                    $quoteExtensionId = substr(
                        $urlCurrent,
                        $positionStartQuoteIdExtension,
                        $positionEndQuoteIdExtension - $positionStartQuoteIdExtension
                    );
                } elseif (str_contains($urlCurrent, 'quoteextension/quote/view')) {
                    $positionStartQuoteIdExtension = strpos(
                            $urlCurrent,
                            '/quote_id/',
                            strpos($urlCurrent, 'quoteextension/quote/view')
                        ) + strlen("/quote_id/");
                    $positionEndQuoteIdExtension = strpos($urlCurrent, '/', $positionStartQuoteIdExtension);
                    $quoteExtensionId = substr(
                        $urlCurrent,
                        $positionStartQuoteIdExtension,
                        $positionEndQuoteIdExtension - $positionStartQuoteIdExtension
                    );
                }
                $this->idQuoteExtensionPlace = $this->getQuoteIdByIdQE($quoteExtensionId);
                $this->checkoutSession->setQuoteIdPayPal($this->idQuoteExtensionPlace);
                $this->checkoutSession->setIsQuoteExtension($this->idQuoteExtensionPlace);
            }
            return $this->idQuoteExtensionPlace;
        } elseif ($this->getQuoteQuoteIdPayPal()) {
            return $this->getQuoteQuoteIdPayPal();
        }
        $this->checkoutSession->setQuoteIdPayPal(null);
        return null;
    }

    /**
     * Get quote by id quote extension
     *
     * @param $quoteExtensionId
     * @return null|int
     */
    public function getQuoteIdByIdQE($quoteExtensionId)
    {
        if (is_numeric($quoteExtensionId)) {
            $manageQuote = $this->manageQuote->create()->load($quoteExtensionId);
            return $manageQuote->getQuoteId();
        }
        return null;
    }

    /**
     * Return quote id of quote extension when place order use payment PayPal
     *
     * @return null|int
     */
    public function getQuoteQuoteIdPayPal()
    {
        $redirectUrl = $this->request->getServer('REDIRECT_URL');
        if ($this->checkoutSession->getQuoteIdPayPal() && $redirectUrl &&
            strpos($redirectUrl, '/paypal/express/return/') !== false
        ) {
            return $this->checkoutSession->getQuoteIdPayPal();
        }
        return null;
    }
}
