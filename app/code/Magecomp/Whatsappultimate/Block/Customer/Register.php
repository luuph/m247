<?php
namespace Magecomp\Whatsappultimate\Block\Customer;

class Register extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
    protected $helpercountryflag;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magecomp\Whatsappcountryflag\Helper\Data $helpercountryflag,
        array $data = []
    ) {
        $this->helpercustomer = $helpercustomer;
        $this->helpercountryflag = $helpercountryflag;
        parent::__construct($context, $data);
    }

    public function getButtonclass()
    {
        return $this->helpercustomer->getButtonclass();
    }

    public function IsSignUpConfirmation()
    {
        return $this->helpercustomer->isSignUpConfirmationForUser();
    }

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
