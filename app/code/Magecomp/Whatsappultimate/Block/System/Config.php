<?php

namespace Magecomp\Whatsappultimate\Block\System;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magecomp\Whatsappultimate\Helper\Apicall;

class Config extends Template
{
    public function __construct(Context $context, StoreManagerInterface $storeManager, Apicall $helperData, array $data = [])
    {
        $this->_storeManager = $storeManager;
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
    }

    public function getStore()
    {

        return $this->_helperData->getStoreid();
    }

    public function getApiGateway()
    {
        $storeId=$this->getStore();
        
        return $this->_helperData->getSelectedGateway($storeId);
    }
}
