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
 * @package    Bss_MinMaxAmountOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxAmountOrderPerCate\Helper;

use Magento\Framework\App\Helper\Context;

class OverviewPost extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var null
     */
    protected $paymentRateLimiter;

    /**
     * @var OverviewPostFactory
     */
    protected $overviewPostFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * OverviewPost constructor.
     * @param Context $context
     * @param OverviewPostFactory $overviewPostFactory
     * @param Data $helper
     * @param null $paymentRateLimiter
     */
    public function __construct(
        Context $context,
        OverviewPostFactory $overviewPostFactory,
        Data $helper,
        $paymentRateLimiter = null
    ) {
        $this->paymentRateLimiter = $paymentRateLimiter;
        $this->helper = $helper;
        $this->overviewPostFactory = $overviewPostFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Checkout\Api\PaymentProcessingRateLimiterInterface|null
     */
    public function getPaymentRateLimiterObject()
    {
        if ($this->helper->versionCompare('2.4.1')) {
            return $this->overviewPostFactory->create($this->paymentRateLimiter);
        }
        return null;
    }
}
