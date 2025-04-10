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

namespace Webkul\Walletsystem\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Webkul Walletsystem Class
 */
class WalletsystemConfigProvider implements ConfigProviderInterface
{
    /**
     * @var methodCode
     */
    protected $methodCode = PaymentMethod::CODE;
    
    /**
     * @var method
     */
    protected $method;
    
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param PaymentHelper $paymentHelper
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }
    /**
     * Get Config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $image = '';
        $walletamount = '';
        $ajaxUrl = '';
        $walletstatus = '';
        $leftToPay = '';
        $leftinwallet = '';
        $currencysymbol = '';
        $getcurrentcode = '';
        $walletformatamount = '';
        $grandTotal = '';
        $paymentTitle = '';
        if ($this->method->isAvailable()) {
            $image = $this->getLoaderImage();
            $walletformatamount = $this->helper->getformattedPrice($this->getWalletamount());
            $walletamount = $this->getWalletamount();
            $ajaxUrl = $this->getAjaxUrl();
            $paymentTitle = $this->helper->getWalletTitle();
            $walletstatus = $this->getWalletStatus();
            $leftToPay = $this->getLeftToPay();
            $leftinwallet = $this->getLeftInWallet();
            $currencysymbol = $this->getCurrencySymbol();
            $getcurrentcode = $this->helper->getCurrentCurrencyCode();
            $grandTotal = $this->helper->getGrandTotal();
        }
        $config['payment']['walletsystem']['loaderimage'] = $image;
        $config['payment']['walletsystem']['paymentTitle'] = $paymentTitle;
        $config['payment']['walletsystem']['getcurrentcode'] = $getcurrentcode;
        $config['payment']['walletsystem']['walletformatamount'] = $walletformatamount;
        $config['payment']['walletsystem']['walletamount'] = $walletamount;
        $config['payment']['walletsystem']['ajaxurl'] = $ajaxUrl;
        $config['payment']['walletsystem']['walletstatus'] = $walletstatus;
        $config['payment']['walletsystem']['leftamount'] = $leftToPay;
        $config['payment']['walletsystem']['leftinwallet'] = $leftinwallet;
        $config['payment']['walletsystem']['currencysymbol'] = $currencysymbol;
        $config['payment']['walletsystem']['grand_total'] = $grandTotal;
        $config['payment']['walletsystem']['walletValue'] = $this->helper->getWalletValueForRadioButton();
        return $config;
    }

    /**
     * Get Loader Image function
     *
     * @return string
     */
    protected function getLoaderImage()
    {
        return $this->method->getLoaderImage();
    }
    
    /**
     * Get Wallet Amount function
     *
     * @return int
     */
    protected function getWalletamount()
    {
        return $this->helper->getWalletTotalAmount(0);
    }
    
    /**
     * Get Ajax Url function
     *
     * @return string
     */
    protected function getAjaxUrl()
    {
        return $this->urlBuilder->getUrl('walletsystem/index/applypaymentamount');
    }
    
    /**
     * Get Wallet Status function
     *
     * @return int
     */
    protected function getWalletStatus()
    {
        return $this->helper->getWalletStatus();
    }
    
    /**
     * Return Left Amount, other than wallet
     *
     * @return int
     */
    protected function getLeftToPay()
    {
        return $this->helper->getlefToPayAmount();
    }
    
    /**
     * Return Left Amount in wallet
     *
     * @return int
     */
    protected function getLeftInWallet()
    {
        return $this->helper->getLeftInWallet();
    }
    
    /**
     * Return Store Cureency Symbol
     *
     * @return character
     */
    protected function getCurrencySymbol()
    {
        return $this->helper->getCurrencySymbol($this->helper->getCurrentCurrencyCode());
    }
}
