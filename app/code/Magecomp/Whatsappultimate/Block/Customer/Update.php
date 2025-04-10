<?php
namespace Magecomp\Whatsappultimate\Block\Customer;

class Update extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
    protected $customersession;
    protected $helpercountryflag;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magento\Customer\Model\Session $customersession,
        \Magecomp\Whatsappcountryflag\Helper\Data $helpercountryflag,
        array $data = []
    ) {
        $this->helpercustomer = $helpercustomer;
        $this->customersession = $customersession;
        $this->helpercountryflag = $helpercountryflag;
        parent::__construct($context, $data);
    }

    public function getButtonclass()
    {
        return $this->helpercustomer->getButtonclass();
    }

    public function getCustomerMobile()
    {
        if ($this->customersession->isLoggedIn()) {
            return $this->customersession->getCustomer()->getMobilenumber();
        }
    }

    public function getCustomerWpterms()
    {
        if ($this->customersession->isLoggedIn()) {
            return $this->customersession->getCustomer()->getWpterms();
        }
    }

   /* public function getDefaultContry()
    {
        return $this->helpercountryflag->getDefaultcontry();
    }*/
    public function getDefaultContry()
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
