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

namespace Webkul\Walletsystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Webkul Walletsystem Class
 */
class Applypaymentamount extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PageFactory $resultPageFactory
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     */
    public function __construct(
        Context $context,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Controller execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->checkoutSession->unsPaypalAmountForWallet();
        $params = $this->getRequest()->getParams();
        $customerId = $this->helper->getCustomerId();
        if (!$customerId) {
            $leftinWallet = $this->helper->getformattedPrice(0);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($myValue);
            return $resultJson;
        }
        $grandtotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $grandtotal = (float) $grandtotal;
        $grandtotal = round($grandtotal, 4);
        $customerId = $this->helper->getCustomerId();
        $amount = $this->helper->getWalletTotalAmount($customerId);
        $store = $this->helper->getStore();
        $converttedAmount = $this->helper->currentCurrencyAmount($amount, $store);
        if ($params['wallet'] == 'set') {
            if ($converttedAmount >= $grandtotal) {
                $discountAmount = $grandtotal;
            } else {
                $discountAmount = $converttedAmount;
            }
            $left = $converttedAmount - $discountAmount;
            $baseLeftAmount = $this->helper->baseCurrencyAmount($left);
            $leftinWallet = $this->helper->getformattedPrice(
                ($baseLeftAmount) > 0 ? $baseLeftAmount : 0
            );
            $myValue = [
                'flag' => 1,
                'amount' => $discountAmount,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
        } else {
            $leftinWallet = $this->helper->getformattedPrice($amount);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
        }
        $this->checkoutSession->getQuote()->collectTotals()->save();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($myValue);
        return $resultJson;
    }
}
