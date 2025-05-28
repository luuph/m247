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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Plugin\Model\Config\Source;

use Bss\CatalogPermission\Helper\ModuleConfig;

class BssListCmsPage
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Field $subject
     * @param array $result
     * @return array
     */
    public function afterGetOptions($subject, $result)
    {
        $data = $subject->getData();
        if (!$this->moduleConfig->enableHomePgae() || !isset($data['id'], $data['path'])) {
            return $result;
        }

        if ($data['id'] == ModuleConfig::HOME_PAGE_REDIRECT &&
            $data['path'] == ModuleConfig::HOME_PAGE_SECTION) {
            foreach ($result as $key => $option) {
                if ($option['value'] == "none") {
                    unset($result[$key]);
                    break;
                }
            }
        }
        return $result;
    }
}
