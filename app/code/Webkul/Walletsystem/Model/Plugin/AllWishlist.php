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
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Walletsystem Class
 */
class AllWishlist extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * Initialize dependencies
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Registry $registry
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Registry $registry,
        Context $context,
        WishlistProviderInterface $wishlistProvider,
        Validator $formKeyValidator,
        ItemCarrier $itemCarrier
    ) {
        $this->walletHelper = $walletHelper;
        $this->messageManager = $messageManager;
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->urlInterface = $urlInterface;
        $this->orderRegistry = $registry;
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return array
     */
    public function execute()
    {
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
            unset($params['form_key']);
            $this->request->setPostValue($params);
        }

        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultForward->setUrl($this->urlInterface->getUrl("wishlist/index/index"));
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
