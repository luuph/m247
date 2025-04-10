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
use Magento\Framework\App\Action;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Wishlist\Helper\Data;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * ShareWishlist resolver
 */
class ShareWishlist extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController implements ResolverInterface
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
     * ScopeConfigInterface variable
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StoreManagerInterface variable
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * WishlistConfig variable
     *
     * @var \Magento\Wishlist\Model\Config
     */
    protected $wishlistConfig;

    /**
     * CustomerFactory variable
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

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
     * TransportBuilder variable
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * InlineTranslation variable
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * CustomerHelperView variable
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $customerHelperView;

    /**
     * Wishlist variable
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * OrderFactory variable
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * WishlistFactory variable
     *
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * Escaper variable
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Emails variable
     *
     * @var string
     */
    protected $emails;
    
    /**
     * URL variable
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    
    /**
     * ItemResolver variable
     *
     * @var ItemResolverInterface
     */
    protected $itemResolver;
    
    /**
     * SharewishlistBlock variable
     *
     * @var \Magento\Wishlist\Block\Share\Email\Items
     */
    protected $sharewishlistBlock;

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\View $customerHelperView
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ItemResolverInterface $itemResolver
     * @param \Magento\Wishlist\Block\Share\Email\Items $sharewishlistBlock
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\View $customerHelperView,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\RequestInterface $request,
        ItemResolverInterface $itemResolver,
        \Magento\Wishlist\Block\Share\Email\Items $sharewishlistBlock,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->emulate = $emulate;
        $this->wishlist = $wishlist;
        $this->jsonHelper = $jsonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->wishlistConfig = $wishlistConfig;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->customerHelperView = $customerHelperView;
        $this->escaper = $escaper;
        $this->itemResolver = $itemResolver;
        $this->sharewishlistBlock = $sharewishlistBlock;
        $this->_url = $url;
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
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $errors = false;
        $emails = [];
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($this->customerId);
        if (!$wishlist) {
            $this->returnArray["message"] = __("Page Not Found.");
            return $this->returnArray;
        }
        // validate the message and emails wishlist with configuration settings//////
        $sharingLimit = $this->wishlistConfig->getSharingEmailLimit();
        $textLimit = $this->wishlistConfig->getSharingTextLimit();
        $wishlist = $this->wishlistProvider->getWishlist();
        $this->wishlist = $wishlist;
        $emailsLeft = $sharingLimit - $wishlist->getShared();
        $emails = empty($this->emails) ? $this->emails : explode(",", $this->emails);
        $message = $this->message;
        if (strlen($this->message) > $textLimit) {
            $this->returnArray["message"] = __("Message length must not exceed %1 symbols", $textLimit);
            return $this->returnArray;
        } else {
            $message = nl2br($this->escaper->escapeHtml($message));
            if (empty($emails)) {
                $this->returnArray["message"] = __("Please enter an email address.");
                return $this->returnArray;
            } else {
                if (count($emails) > $emailsLeft) {
                    $this->returnArray["message"] = __(
                        "Sharing Limit over.This wish list can be shared %1 more times.",
                        $emailsLeft
                    );
                    return $this->returnArray;
                } else {
                    foreach ($emails as $index => $email) {
                        $email = trim($email);
                        $emailValidator = new \Laminas\Validator\EmailAddress();
                        if (!$emailValidator->isValid($email)) {
                            $this->returnArray["message"] = __("Please enter a valid email.");
                            return $this->returnArray;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }
        // sending email ////////////////////////////////////////////////////////////
        $this->returnArray["success"] = true;
        $this->returnArray["message"] = __("Wishlist shared successfully.");
        $this->sendEmails($emails, $wishlist);
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
        $this->returnArray["success"] = false;
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->emails = $this->wholeData["emails"] ?? "";
            $this->message = $this->wholeData["message"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->customerSession->setCustomerId($this->customerId);
            }
            if ($this->emails == "" || $this->message == "") {
                $this->returnArray["message"] = __("Invalid Data.");
                $this->returnArray["otherError"] = __("Missing Required Information");
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to send email
     *
     * @param array  $emails   array of emails
     * @param object $wishlist wishlist
     *
     * @return bool
     */
    public function sendEmails($emails, $wishlist)
    {
        $sent = 0;
        $customer = $this->customerSession->getCustomerDataObject();
        $customerName = $this->customerHelperView->getCustomerName($customer);
        $this->inlineTranslation->suspend();
        $message = $this->message;
        $emails = array_unique($emails);
        $emailsSentTo = "";
        $sharingCode = $wishlist->getSharingCode();
        try {
            foreach ($emails as $email) {
                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $this->scopeConfig->getValue(
                        "wishlist/email/email_template",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setTemplateOptions(
                    [
                        "area" => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getStoreId(),
                    ]
                )->setTemplateVars(
                    [
                        "store" => $this->storeManager->getStore(),
                        "items" => $this->getWishlistItems(),
                        "salable" => $wishlist->isSalable() ? "yes" : "",
                        "message" => $message,
                        "customer" => $customer,
                        "customerName" => $customerName,
                        "viewOnSiteLink" => $this->_url->getUrl("wishlist/shared/index", ["code"=>$sharingCode])
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        "wishlist/email/email_identity",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->addTo(
                    $email
                )->getTransport();
                $transport->sendMessage();
                $emailsSentTo .= $email.",";
                $sent++;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();
            $this->returnArray["sentTo"] = $emailsSentTo;
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->returnArray["success"] = false;
            $this->returnArray["message"] = $e->getMessage();
        }
    }

    /**
     * Retrieve wishlist items content (html)
     *
     * @return string
     */
    protected function getWishlistItems()
    {
        $block = $this->wishlist;
        $l = $block->getItemsCount();
        $html = '<div>
        <table>
            <tr>';
                $i = 0;
                foreach ($block->getItemCollection() as $item){
                    $_product = $item->getProduct();
                    $html .= '<td class="col product">
                        <p>
                            <a href="'.$this->escaper->escapeUrl($block->getProductUrl($item)).'">';
                                $productThumbnail = $this->itemResolver->getFinalProduct($item);
                                $currentStore = $this->storeManager->getStore($this->storeId);
                                $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                                $thumbnailImage = $mediaUrl.'catalog/product'.$productThumbnail->getData('thumbnail');
                                $html .= '<img src="'.$thumbnailImage.'" width="150" height="150">
                            </a>
                        </p>
    
                        <p>
                            <a href="'.$this->sharewishlistBlock->escapeUrl($block->getProductUrl($item)) .'">
                                <strong>'. $this->sharewishlistBlock->escapeHtml($_product->getName()) .'</strong>
                            </a>
                        </p>';
                        if($block->hasDescription($item)){
                            $html .= '<p>
                                <strong>'. $this->sharewishlistBlock->escapeHtml(__('Comment')) .':</strong>
                                <br/>'. $block->getEscapedDescription($item) .'
                            </p>';
                        }
                        $html .= '<p>
                            <a href="'. $this->sharewishlistBlock->escapeUrl($block->getProductUrl($item)) .'">
                                '. $this->sharewishlistBlock->escapeHtml(__('View Product')) .'
                            </a>
                        </p>
                    </td>';
                    if ($i % 3 != 0) {
                        $html .= '<td></td>';
                    }
                    $html .= '</tr>
                <tr>
                    <td colspan="5">&nbsp;</td>
                </tr>';
                if ($i < $l){
                    $html .= '<tr>';
                }
                 }
            
            $html .= '</table>
    </div>';
    return $html;
    }

    /**
     * Retrieve RSS link content (html)
     *
     * @param int                                   $wishlistId   wishlistId
     * @param \Magento\Framework\View\Result\Layout $resultLayout resultLayout
     *
     * @return mixed
     */
    protected function getRssLink($wishlistId, ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam("rss_url")) {
            return $resultLayout->getLayout()
                ->getBlock("wishlist.email.rss")
                ->setWishlistId($wishlistId)
                ->toHtml();
        }
    }

    /**
     * Prepare to load additional email blocks
     *
     * Add "wishlist_email_rss" layout handle.
     *
     * Add "wishlist_email_items" layout handle.
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     *
     * @return void
     */
    protected function addLayoutHandles(ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam("rss_url")) {
            $resultLayout->addHandle("wishlist_email_rss");
        }
        $resultLayout->addHandle("wishlist_email_items");
    }
}
