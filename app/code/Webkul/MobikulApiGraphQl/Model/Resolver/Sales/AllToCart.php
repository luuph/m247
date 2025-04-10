<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Checkout\Model\Cart;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Wishlist\Helper\Data as WishlistHelper;

/**
 * AllToCart resolver
 */
class AllToCart extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController implements ResolverInterface
{
    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * CustomerSession variable
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * WishlistProvider variable
     *
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * CustomerHelperView variable
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $customerHelperView;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * Cart variable
     *
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * ItemCarrier variable
     *
     * @var Magento\Wishlist\Model\ItemCarrier
     */
    protected $itemCarrier;

    /**
     * QuoteFactory variable
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * WishlistFactory variable
     *
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * QuantityProcessor variable
     *
     * @var Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * Constructor function
     *
     * @param Cart $cart
     * @param ItemCarrier $itemCarrier
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\View $customerHelperView
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param WishlistHelper $wishlistHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Cart $cart,
        ItemCarrier $itemCarrier,
        \Webkul\MobikulCore\Helper\Data $helper,
        LocaleQuantityProcessor $quantityProcessor,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\View $customerHelperView,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        WishlistHelper $wishlistHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->cart = $cart;
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->itemCarrier = $itemCarrier;
        $this->quoteFactory = $quoteFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->customerHelperView = $customerHelperView;
        $this->wishlistHelper = $wishlistHelper;
        parent::__construct($helper, $request, $jsonHelper);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        $this->verifyRequest();
        if ($this->customerId == 0 && $this->customerId == "") {
            $this->returnArray["message"] = __("As customer you are requesting does not exist, so you need to logout.");
            return $this->returnArray;
        }
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $this->customerSession->setCustomerId($this->customerId);
        $errors = false;
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($this->customerId, true);
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();
        $cart = $this->cart;
        $isOwner = $wishlist->isOwner($this->customerId);
        $notSalable = [];
        $addedProducts = [];
        $this->returnArray['warning'] = false;
        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();
                // Set qty //////////////////////////////////////////////////////////
                $key = array_search($item->getId(), array_column($this->itemData, 'id'));
                if (isset($this->itemData[$key]['id'])) {
                    $qty = $this->quantityProcessor->process($this->itemData[$key]['qty']);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                if ($item->addToCart($cart, $isOwner)) {
                    $addedProducts[] = $item->getProduct();
                }
            } catch (\Exception $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = $item;
                } else {
                    $this->returnArray['warning'] = true;
                    $this->returnArray["message"] .= __(
                        '%1 for "%2".',
                        trim($e->getMessage(), "."),
                        $item->getProduct()->getName()
                    );
                }
                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (\LocalizedException $e) {
                $this->returnArray['warning'] = true;
                $this->returnArray['message'] = __("We can't add this item to your shopping cart right now.");
            }
        }
        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $this->returnArray['warning'] = true;
            $this->returnArray["message"] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }

        if ($addedProducts) {
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->returnArray["success"] = false;
                $this->returnArray['warning'] = true;
                $this->returnArray["message"] = __('We can\'t update the Wish List right now.');
                return $this->returnArray;
            }
            $products = [];
            foreach ($addedProducts as $product) {
                /** @var $product \Magento\Catalog\Model\Product */
                $products[] = '"' . $product->getName() . '"';
            }
            $this->returnArray["message"] .= __(
                '%1 product(s) have been added to shopping cart: %2.',
                count($addedProducts),
                join(', ', $products)
            );
            // save cart and collect totals /////////////////////////////////////////
            $cart->save()->getQuote()->collectTotals();
            $this->returnArray["cartCount"] = (int) $cart->getQuote()->getItemsCount();
        }
        $cart->save()->getQuote()->collectTotals();
        $this->returnArray["cartCount"] = (int) $cart->getQuote()->getItemsCount();
        $this->wishlistHelper->calculate();
        $this->returnArray["success"] = true;
        $this->emulate->stopEnvironmentEmulation($environment);
        return $this->returnArray;
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
            $this->itemData = $this->wholeData['itemData'] ?? [];
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
