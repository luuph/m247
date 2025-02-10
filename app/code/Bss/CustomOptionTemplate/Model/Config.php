<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* @category   BSS
* @package    Bss_CustomOptionTemplate
* @author     Extension Team
* @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\CustomOptionTemplate\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_MODE_SAVE = 'bss_custom_option_template/advanced_setting/save_mode';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get config mode save
     *
     * @return bool
     */
    public function getConfigSaveMode()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MODE_SAVE,
            ScopeInterface::SCOPE_STORE
        );
    }

}
