<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block\Adminhtml\Notification\Edit\Tab;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class CustomCollectionNotification block
 */
class CustomCollectionNotification extends \Magento\Backend\Block\Template
{
    /**
     * UrlHelper variable
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    public $urlHelper;
    
    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    
    /**
     * Request variable
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * Combine variable
     *
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    protected $combine;
    
    /**
     * NotificationRepository variable
     *
     * @var \Webkul\MobikulCore\Api\NotificationRepositoryInterface
     */
    protected $notificationRepository;

    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Backend\Model\UrlInterface $urlHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\Combine $combine
     * @param \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepository
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\UrlInterface $urlHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product\Combine $combine,
        \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepository,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        array $data = []
    ) {
        $this->request = $request;
        $this->combine = $combine;
        $this->urlHelper = $urlHelper;
        $this->jsonHelper = $jsonHelper;
        $this->notificationRepository = $notificationRepository;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Function to prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("notification/customcollectionnotification.phtml");
    }

    /**
     * Function  to get Assigned attributes
     *
     * @return string
     */
    public function getAssignedAttributes()
    {
        $options = $this->combine->getNewChildSelectOptions();
        return $options[3]["value"];
    }

    /**
     * Function to get product jSon
     *
     * @return json
     */
    public function getProductsJson()
    {
        $notification = $this->getNotificationData();
        if (count($notification) && $notification["collection_type"] == "product_ids") {
            $filterData = $this->serializer->unserialize($notification["filter_data"]);
            $productIds = explode(",", $filterData);
        } else {
            $productIds = [];
        }
        return $this->jsonHelper->jsonEncode($productIds);
    }

    /**
     * Function get Notification Data
     *
     * @return array
     */
    public function getNotificationData()
    {
        $notificationId = $this->request->getParam("id");
        if ($notificationId) {
            $notification = $this->notificationRepository->getById($notificationId);
            return $notification->getData();
        } else {
            return [];
        }
    }

    /**
     * Serializer function
     *
     * @return void
     */
    public function serializer()
    {
        return $this->serializer;
    }
}
