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

namespace Webkul\Walletsystem\Model\Plugin;

use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul Walletsystem Class
 */
class Cart
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $orderRegistry;
    /**
     * @var CartData
     */
    protected $cartData;
    /**
     * @var walletProductId
     */
    protected $walletProductId;

    /**
     * Constructor
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->walletHelper = $walletHelper;
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->orderRegistry = $registry;
    }

    /**
     * Before Add Produt
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param Collection $productInfo
     * @param Collection $requestInfo
     * @return array
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $params = $this->request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $this->walletProductId = $this->walletHelper->getWalletProductId();
        if (array_key_exists('product', $params)) {
            $productId = $params['product'];
        } elseif (array_key_exists('order_id', $params)) {
            $order = $this->orderRegistry->registry('current_order');
            $items = $order->getItemsCollection();
        }
        $quote = $this->quote;
        $this->cartData = $quote->getAllItems();
        if ($productId) {
            $this->checkWalletProductId($productId);
        } elseif (!empty($items)) {
            $walletInOrder = $this->checkIfOrderHaveWalletProduct($items, $this->walletProductId);
            if (!empty($this->cartData)) {
                foreach ($this->cartData as $item) {
                    $itemProductId = $item->getProductId();
                    if ($this->walletProductId == $itemProductId) {
                        if (!$walletInOrder) {
                            throw new LocalizedException(__('You can not add other product with wallet product'));
                        }
                    } else {
                        if ($walletInOrder) {
                            throw new LocalizedException(__('You can not add wallet product with other product'));
                        }
                    }
                }
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Check if order have wallet product
     *
     * @param array $items
     * @param int $walletProductId
     * @return boolean
     */
    public function checkIfOrderHaveWalletProduct($items, $walletProductId)
    {
        foreach ($items as $item) {
            $productId = $item->getproduct()->getId();
            if ($productId == $this->walletProductId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check wallet product id
     *
     * @param int $productId
     */
    public function checkWalletProductId($productId)
    {
        $flag = 0;
        if ($this->walletProductId == $productId) {
            $flag = 1;
        }
        if (!empty($this->cartData)) {
            foreach ($this->cartData as $item) {
                $itemProductId = $item->getProductId();
                if ($this->walletProductId == $itemProductId) {
                    if ($flag != 1) {
                        throw new LocalizedException(__('You can not add other product with wallet product'));
                    }
                } else {
                    if ($flag == 1) {
                        throw new LocalizedException(__('You can not add wallet product with other product'));
                    }
                }
            }
        }
    }
}
