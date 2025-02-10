<?php
namespace MageArray\Popup\Block\Popup;

/**
 * Class PopupList
 * @package MageArray\Popup\Block\Popup
 */
class PopupList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MageArray\Popup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MageArray\Popup\Model\PopupFactory
     */
    protected $_popupFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkout;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * PopupList constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageArray\Popup\Model\PopupFactory $popupFactory
     * @param \MageArray\Popup\Helper\Data $dataHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkout
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Customer\Model\Session $authSession
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Element\Template\Context $context,
        \MageArray\Popup\Model\PopupFactory $popupFactory,
        \MageArray\Popup\Helper\Data $dataHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkout,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $authSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($context);
        $this->_cart = $cart;
        $this->_checkout = $checkout;
        $this->_popupFactory = $popupFactory;
        $this->_dataHelper = $dataHelper;
        $this->_resource = $resource;
        $this->_request = $request;
        $this->_authSession = $authSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_urlInterface = $context->getUrlBuilder();
        $this->orders=[];
    }

    /**
     * @param $id
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPopup()
    {
        $collection = $this->_popupFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', '1');

        if (!count($collection)) {
            return false;
        }
        return $collection->getData();
    }

    /**
     * @return mixed
     */
    public function getContent($content)
    {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $filterManager = $om->get(\Magento\Cms\Model\Template\FilterProvider::Class)->getPageFilter()->filter($content);
        return $filterManager;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrl()
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\UrlInterface::Class);
        return $urlInterface->getCurrentUrl();
    }

    /**
     * @return bool
     */
    public function getSubtotal()
    {
        $cartQuote = $this->_cart->getQuote()->getData();
        if (!empty($cartQuote['items_count'])) {
            return number_format($cartQuote['subtotal'], 2);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * @return mixed
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create(\Magento\Customer\Model\Session::Class);
        return $customerSession->getCustomerId();
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function getOrders()
    {
        $customerId = $this->getCurrentUser();
        if ($customerId) {
            if (!$this->orders) {
                $this->orders = $this->_orderCollectionFactory->create()
                    ->addFieldToSelect(
                        'status'
                    )->addFieldToFilter(
                        'customer_id',
                        $customerId
                    )->addFieldToFilter(
                        'status',
                        'pending'
                    )->setOrder(
                        'created_at',
                        'desc'
                    );
            }
            return count($this->orders->getData());
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Popup::popup');
    }
}
