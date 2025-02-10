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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionTemplate\Model\System\Message;

class CoreModuleRequired implements \Magento\Framework\Notification\MessageInterface
{
    const MESSAGE_IDENTITY = 'bss_option_core_module_required_of_option_template';
    const BSS_OPTION_CORE_MODULE_NAME = 'Bss_CustomOptionCore';
    const MODULE_NAME = 'Bss_CustomOptionTemplate';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * CoreModuleRequired constructor.
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     */
    protected function checkOptionCoreModuleInstall()
    {
        return $this->moduleManager->isEnabled(self::BSS_OPTION_CORE_MODULE_NAME);
    }

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // The message will be shown
        return !$this->checkOptionCoreModuleInstall();
    }

    /**
     * Retrieve system message text
     *
     * @return string
     */
    public function getText()
    {
        $text = __(
            '<b>Your module "%1" can not work without BSS Commerce\'s 
                Option Core Module included in the package</b>',
            self::MODULE_NAME
        );
        $script =
            '<script>
                setTimeout(function() {
                    jQuery("button.message-system-action-dropdown").trigger("click");
                }, 100);
            </script>';
        return $text . $script;
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
