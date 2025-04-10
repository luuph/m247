<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApiGraphQl\Model\Resolver;

if (!defined('DS')) {
    define("DS", DIRECTORY_SEPARATOR);
}
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * ApiController abstract class
 */
abstract class ApiController
{
    /**
     * Os variable
     *
     * @var string
     */
    protected $os;
    
    /**
     * URL variable
     *
     * @var string
     */
    protected $url;
    
    /**
     * DOB variable
     *
     * @var mixed
     */
    protected $dob;
    
    /**
     * Qty variable
     *
     * @var mixed
     */
    protected $qty;
    
    /**
     * ETag variable
     *
     * @var string
     */
    protected $eTag;
    
    /**
     * Hash variable
     *
     * @var mixed
     */
    protected $hash;
    
    /**
     * Width variable
     *
     * @var mixed
     */
    protected $width;
    
    /**
     * Token variable
     *
     * @var mixed
     */
    protected $token;
    
    /**
     * Email variable
     *
     * @var mixed
     */
    protected $email;
    
    /**
     * Title variable
     *
     * @var string
     */
    protected $title;
    
    /**
     * Height variable
     *
     * @var mixed
     */
    protected $height;
    
    /**
     * Mobile variable
     *
     * @var mixed
     */
    protected $mobile;
    
    /**
     * Prefix variable
     *
     * @var mixed
     */
    protected $prefix;
    
    /**
     * Suffix variable
     *
     * @var mixed
     */
    protected $suffix;
    
    /**
     * Taxvat variable
     *
     * @var mixed
     */
    protected $taxvat;
    
    /**
     * ItemId variable
     *
     * @var mixed
     */
    protected $itemId;
    
    /**
     * Gender variable
     *
     * @var string
     */
    protected $gender;
    
    /**
     * Detail variable
     *
     * @var mixed
     */
    protected $detail;

    /**
     * Helper variable
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * QuoteId variable
     *
     * @var mixed
     */
    protected $quoteId;
    
    /**
     * MFactor variable
     *
     * @var mixed
     */
    protected $mFactor;
    
    /**
     * StoreId variable
     *
     * @var mixed
     */
    protected $storeId;
    
    /**
     * Ratings variable
     *
     * @var mixed
     */
    protected $ratings;
    
    /**
     * Headers variable
     *
     * @var mixed
     */
    protected $headers;
    
    /**
     * Product variable
     *
     * @var mixed
     */
    protected $product;
    
    /**
     * Message variable
     *
     * @var mixed
     */
    protected $message;
    
    /**
     * Customer variable
     *
     * @var mixed
     */
    protected $customer;
    
    /**
     * Username variable
     *
     * @var string
     */
    protected $username;
    
    /**
     * Password variable
     *
     * @var string
     */
    protected $password;
    
    /**
     * IsSocial variable
     *
     * @var boolean
     */
    protected $isSocial;
    
    /**
     * LastName variable
     *
     * @var string
     */
    protected $lastName;
    
    /**
     * ReviewId variable
     *
     * @var mixed
     */
    protected $reviewId;
    
    /**
     * Nickname variable
     *
     * @var string
     */
    protected $nickname;
    
    /**
     * ItemData variable
     *
     * @var mixed
     */
    protected $itemData;
    
    /**
     * IconWidth variable
     *
     * @var mixed
     */
    protected $iconWidth;
    
    /**
     * IsFromUrl variable
     *
     * @var boolean
     */
    protected $isFromUrl;
    
    /**
     * WebsiteId variable
     *
     * @var mixed
     */
    protected $websiteId;
    
    /**
     * WholeData variable
     *
     * @var mixed
     */
    public $wholeData;
    
    /**
     * AddressId variable
     *
     * @var mixed
     */
    protected $addressId;
    
    /**
     * FirstName variable
     *
     * @var mixed
     */
    protected $firstName;
    
    /**
     * ItemBlock variable
     *
     * @var mixed
     */
    protected $itemBlock;
    
    /**
     * ProductId variable
     *
     * @var mixed
     */
    protected $productId;
    
    /**
     * CarouselId variable
     *
     * @var mixed
     */
    protected $carouselId;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Collection variable
     *
     * @var mixed
     */
    protected $collection;
    
    /**
     * CustomerId variable
     *
     * @var mixed
     */
    protected $customerId;
    
    /**
     * IconHeight variable
     *
     * @var mixed
     */
    protected $iconHeight;
    
    /**
     * PageNumber variable
     *
     * @var mixed
     */
    protected $pageNumber;
    
    /**
     * PriceBlock variable
     *
     * @var mixed
     */
    protected $priceBlock;
    
    /**
     * PictureURL variable
     *
     * @var string
     */
    protected $pictureURL;
    
    /**
     * MiddleName variable
     *
     * @var string
     */
    protected $middleName;
    
    /**
     * AddressData variable
     *
     * @var mixed
     */
    protected $addressData;
    
    /**
     * NewPassword variable
     *
     * @var string
     */
    protected $newPassword;
    
    /**
     * IncrementId variable
     *
     * @var mixed
     */
    protected $incrementId;
    
    /**
     * LoadedOrder variable
     *
     * @var mixed
     */
    protected $loadedOrder;
    
    /**
     * BannerWidth variable
     *
     * @var string
     */
    protected $bannerWidth;
    
    /**
     * CustomerName variable
     *
     * @var string
     */
    protected $customerName;
    
    /**
     * ProfileWidth variable
     *
     * @var string
     */
    protected $profileWidth;
    
    /**
     * RecipientName variable
     *
     * @var string
     */
    protected $recipientName;
    
    /**
     * CustomerEmail variable
     *
     * @var string
     */
    protected $customerEmail;
    
    /**
     * CustomerToken variable
     *
     * @var string
     */
    protected $customerToken;
    
    /**
     * ProfileHeight variable
     *
     * @var string
     */
    protected $profileHeight;
    
    /**
     * DoChangeEmail variable
     *
     * @var boolean
     */
    protected $doChangeEmail;
    
    /**
     * RecipientEmail variable
     *
     * @var string
     */
    protected $recipientEmail;
    
    /**
     * CollectionType variable
     *
     * @var mixed
     */
    protected $collectionType;
    
    /**
     * ConfirmPassword variable
     *
     * @var string
     */
    protected $confirmPassword;
    
    /**
     * CurrentPassword variable
     *
     * @var string
     */
    protected $currentPassword;
    
    /**
     * ProductCarousel variable
     *
     * @var mixed
     */
    protected $productCarousel;
    
    /**
     * ReturnArray variable
     *
     * @var array
     */
    public $returnArray = [];
    
    /**
     * DoChangePassword variable
     *
     * @var boolean
     */
    protected $doChangePassword;

    /**
     * Request variable
     *
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * OtherError variable
     *
     * @var boolean
     */
    protected $otherError = false;

    /**
     * Constructor function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->returnArray["success"] = false;
        $this->returnArray["message"] = "";
    }

    /**
     * GetRequest function
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Check wheather order can be reordered or not.
     *
     * @param \Magento\Sales\Model\Order $order order
     *
     * @return integer
     */
    public function canReorder(\Magento\Sales\Model\Order $order)
    {
        if (!$this->helper->getConfigData("sales/reorder/allow")) {
            return 0;
        } else {
            return $order->canReorder();
        }
    }

    /**
     * Check wheather order can be reordered or not.
     *
     * @param string $cacheString cache string
     *
     * @return null
     */
    public function checkNGenerateEtag($cacheString)
    {
        if ($this->helper->getConfigData("mobikul/cachesettings/enable")) {
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
        }
    }
}
