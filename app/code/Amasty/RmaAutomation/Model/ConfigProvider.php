<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amrmaaut/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const XPATH_ENABLED = 'general/enabled';
    /**#@-*/

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }
}
