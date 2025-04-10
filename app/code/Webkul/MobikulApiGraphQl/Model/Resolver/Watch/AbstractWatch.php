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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Watch;

/**
 * AbstractWatch abstract class
 */
abstract class AbstractWatch extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController
{
    /**
     * @var string
     */
    protected const QRBASEURL = "https://chart.googleapis.com/chart";

    /**
     * @var string
     */
    protected const FCMBASEURL = "https://fcm.googleapis.com/fcm/send";

    /**
     * @var  \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var  \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var  string|null
     */
    protected $firebaseId;

    /**
     * @var  \Webkul\MobikulCore\Model\WatchFactory
     */
    protected $watchFactory;

    /**
     * @var  \Webkul\MobikulCore\Model\ResourceModel\Watch\CollectionFactory
     */
    protected $watchCollectionFactory;

    /**
     * @var  \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encrypted;

    /**
     * @var \Webkul\MobikulCore\Helper\Token
     */
    protected $tokenHelper;

    /**
     * Construct function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Webkul\MobikulCore\Model\WatchFactory $watchFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encrypted
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Webkul\MobikulCore\Helper\Token $tokenHelper
     */
    public function __construct(       
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\MobikulCore\Model\WatchFactory $watchFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Watch\CollectionFactory $watchCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encrypted,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Webkul\MobikulCore\Helper\Token $tokenHelper
    ) {
        $this->watchFactory = $watchFactory;
        $this->watchCollectionFactory = $watchCollectionFactory;
        $this->eventManager = $eventManager;
        $this->encrypted = $encrypted;
        $this->curl = $curl;
        $this->tokenHelper = $tokenHelper;
        parent::__construct($helper, $request, $jsonHelper);
    }
}
