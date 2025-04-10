<?php
namespace Webkul\Walletsystem\Model\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class Wishlist
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $orderRegistry;

    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->walletHelper = $walletHelper;
        $this->messageManager = $messageManager;
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->orderRegistry = $registry;
    }

    public function beforeExecute(
        \Magento\Wishlist\Controller\Index\Cart $subject
    ) {
        $params = $this->request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $walletProductId = $this->walletHelper->getWalletProductId();

        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        if (!empty($cartData)) {
            foreach ($cartData as $item) {
                if ($item->getProductId() == $walletProductId) {
                    $flag = true;
                }
            }
        }
        if ($flag) {
            $this->messageManager->addError(__('You can not add other product with wallet product'));
            unset($params['item']);
            return $this->request->setPostValue($params);
        }
    }
}
