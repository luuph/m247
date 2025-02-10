<?php
namespace Sunarc\Custommenu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
        parent::__construct($context);
    }

    public function isEnable()
    {
        return $this->scopeConfig->getValue('sunarc_custommenu/general/enabled', ScopeInterface::SCOPE_STORE);
    }
    public function getFirstMenu()
    {
        return $this->scopeConfig->getValue('sunarc_custommenu/general/firstmenu', ScopeInterface::SCOPE_STORE);
    }
    public function getSecondMenu()
    {
        return $this->scopeConfig->getValue('sunarc_custommenu/general/secondmenu', ScopeInterface::SCOPE_STORE);
    }
    public function getThirdMenu()
    {
        return $this->scopeConfig->getValue('sunarc_custommenu/general/thirdmenu', ScopeInterface::SCOPE_STORE);
    }
}