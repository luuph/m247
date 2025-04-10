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

namespace Webkul\MobikulCore\Model\Config\Backend;

/**
 * Class LauncherZip model
 */
class LauncherZip extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * GetAllowedExtensions
     *
     * @return string[]
     */
    public function _getAllowedExtensions()
    {
        return ['zip'];
    }
}
