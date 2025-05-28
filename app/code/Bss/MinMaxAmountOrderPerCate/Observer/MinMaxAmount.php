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
namespace Bss\MinMaxAmountOrderPerCate\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Bss\MinMaxAmountOrderPerCate\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\CategoryFactory;

class MinMaxAmount implements ObserverInterface
{
    /**
     * SessionFactory
     *
     * @var SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $minmaxHelper;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * MinMaxAmount constructor.
     * @param SessionFactory $customerSessionFactory
     * @param Http $request
     * @param ManagerInterface $messageManager
     * @param Data $minmaxHelper
     * @param Cart $cart
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        SessionFactory $customerSessionFactory,
        Http $request,
        ManagerInterface $messageManager,
        Data $minmaxHelper,
        Cart $cart,
        CategoryFactory $categoryFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->minmaxHelper = $minmaxHelper;
        $this->cart = $cart;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->minmaxHelper->getConfig('enable')) {
            $items_name = [];
            $amount_categories = [];
            $customerGroupId = 0;
            $quoteItems = $this->cart->getQuote()->getAllVisibleItems();
            //checkout with multiple shipping
            if ($this->request->getFullActionName() === 'multishipping_checkout_overviewPost') {
                $order = $observer->getEvent()->getOrder();
                $quoteItems = $order->getAllItems();
            }
            foreach ($quoteItems as $item) {
                $categories = $item->getProduct()->getCategoryIds();
                $items_name[$item->getName()] = $categories;

                foreach ($categories as $cate) {
                    if (is_array($amount_categories) && !empty($amount_categories[$cate])) {
                        $amount_categories[$cate] += $item->getRowTotalInclTax();
                    } else {
                        $amount_categories[$cate] = $item->getRowTotalInclTax();
                    }
                }
            }
            $customer = $this->customerSessionFactory->create();
            if ($customer->isLoggedIn()) {
                $customerGroupId = $customer->getCustomer()->getGroupId();
            }
            $this->minmaxHelper->validateMinMaxAmount($amount_categories, $items_name, $customerGroupId);
        }
    }
}
