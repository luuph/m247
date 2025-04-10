<?php
namespace Magecomp\Whatsappultimate\Block\Customer;

class Checkout extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
    protected $_urlManager;
    protected $helpercountryflag;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magento\Framework\UrlInterface $urlManager,
        \Magecomp\Whatsappcountryflag\Helper\Data $helpercountryflag,
        array $data = []
    ) {
        $this->helpercustomer = $helpercustomer;
        $this->_urlManager = $urlManager;
        $this->helpercountryflag = $helpercountryflag;
        parent::__construct($context, $data);
    }

    public function getButtonclass()
    {
        return $this->helpercustomer->getButtonclass();
    }

    public function getCheckoutURL()
    {
        return $this->_urlManager->getUrl('checkout/index/index', ['_secure' => true]);
    }

    public function getDefaultCountry()
    {
        return $this->helpercountryflag->getDefualtCountry();
    }
    public function getCountryvalidation($country)
    {
        return $this->helpercountryflag->getCountryvalidation($country);
    }
    public function getGeoCountryCode()
    {
        return $this->helpercountryflag->getCustomerIPDetails();
    }
    public function getCountryFlagEnable()
    {
        return $this->helpercountryflag->isEnabled() && $this->helpercountryflag->isMainEnabled();
    }
    public function getCountryFlagDetectByIP()
    {
        return $this->helpercountryflag->getCustomerIPDetails() && $this->helpercountryflag->getDetectByIp();
    }
}
