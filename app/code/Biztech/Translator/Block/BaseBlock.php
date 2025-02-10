<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block;

use Magento\Framework\UrlFactory;

class BaseBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Biztech\Translator\Helper\Data
     */
    protected $_devToolHelper;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_urlApp;

    /**
     * @var \Biztech\Translator\Model\Config
     */
    protected $_config;

    /**
     * @param \Biztech\Translator\Block\Context $context
     * @param \Magento\Framework\UrlFactory $urlFactory
     */
    public function __construct(\Biztech\Translator\Block\Context $context)
    {
        $this->_devToolHelper = $context->getTranslatorHelper();
        $this->_config = $context->getConfig();
        $this->_urlApp = $context->getUrlFactory()->create();
        parent::__construct($context);
    }

    /**
     * Function for getting event details
     * @return array
     */
    public function getEventDetails()
    {
        return $this->_devToolHelper->getEventDetails();
    }

    /**
     * Function for getting current url
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlApp->getCurrentUrl();
    }

    /**
     * Function for getting controller url for given router path
     * @param string $routePath
     * @return string
     */
    public function getControllerUrl($routePath)
    {

        return $this->_urlApp->getUrl($routePath);
    }

    /**
     * Function for getting current url
     * @param string $path
     * @return string
     */
//    public function getConfigValue($path)
//    {
//        return $this->_config->getCurrentStoreConfigValue($path);
//    }
}
