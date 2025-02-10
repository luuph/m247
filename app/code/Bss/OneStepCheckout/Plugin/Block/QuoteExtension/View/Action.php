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
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin\Block\QuoteExtension\View;

class Action
{
    /**
     * OneStep checkout helper config
     *
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkoutSession;

    /**
     * Construct
     *
     * @param \Bss\OneStepCheckout\Helper\Config $configHelper
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     */
    public function __construct(
        \Bss\OneStepCheckout\Helper\Config $configHelper,
        \Magento\Checkout\Model\SessionFactory$checkoutSession
    ) {
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Compatible with OneStepCheckout
     *
     * @param object $subject
     * @param string $result
     * @param string $action
     * @return string
     */
    public function afterGetAction($subject, $result, $action){
        if ($this->configHelper->isEnabled() && $this->configHelper->isShowBssCheckoutPage() && $action == "quoteextension"){
            $router = $this->configHelper->getGeneral('router_name');
            if (!$router) {
                $router = 'onestepcheckout';
            }
            $quoteId = $this->checkoutSession->create()->getIsQuoteExtension();
            return $subject->getUrl($router,['_query' =>
                [
                    'quoteextension' => '1',
                    'quote_id' => $quoteId
                ],
            ]);
        }
        return $result;
    }
}
