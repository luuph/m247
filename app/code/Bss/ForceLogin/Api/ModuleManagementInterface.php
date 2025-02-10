<?php
declare(strict_types=1);
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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Api;

/**
 * Interface ModuleManagementInterface
 */
interface ModuleManagementInterface
{
    /**
     * Get module configs
     *
     * @param int|null $storeId
     * @return \Bss\ForceLogin\Api\Data\ModuleConfigInterface
     */
    public function getModuleConfigs($storeId = null);
}
