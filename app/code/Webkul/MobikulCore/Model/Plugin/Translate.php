<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Plugin;

/**
 * Class Translate model
 */
class Translate
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct function
     *
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->_store = $store;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * AfterGetLocale Function
     *
     * @param \Magento\Store\Model\Store $subject
     * @param string $result
     * @return string
     */
    public function afterGetLocale(
        \Magento\Framework\Translate $subject,
        $result
    ) {
        if ($this->request->getHeader("authKey") && $this->request->getParam("storeId")) {
            return $this->scopeConfig->getValue(
                "general/locale/code",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->request->getParam("storeId")
            );
        }
        return $result;
    }
}
