<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\ViewModel;

use Webkul\Walletsystem\Helper\Data;

/**
 * Webkul Walletsystem ViewModel
 */
class ViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Initialize dependencies
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get Helper Clas Object
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
    /**
     * Is Module enable
     *
     * @param string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->helper->isModuleEnabled($moduleName);
    }
}
