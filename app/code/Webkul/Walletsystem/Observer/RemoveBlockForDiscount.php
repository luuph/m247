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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Walletsystem Class
 */
class RemoveBlockForDiscount implements ObserverInterface
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->walletHelper = $walletHelper;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('checkout.cart.coupon');

        if ($block) {
            if (!$this->walletHelper->getDiscountEnable() && !$this->walletHelper->getCartStatus()) {
                $layout->unsetElement('checkout.cart.coupon');
                return $this;
            }
            return $this;
        }
        return $this;
    }
}
