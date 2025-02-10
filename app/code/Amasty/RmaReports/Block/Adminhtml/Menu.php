<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Block\Adminhtml;

class Menu extends Navigation
{
    /**
     * @return array
     */
    public function getMenu()
    {
        $config = $this->getConfig();

        foreach ($config as $groupKey => &$group) {
            if (isset($group['resource']) && !$this->_authorization->isAllowed($group['resource'])) {
                unset($config[$groupKey]);
                continue;
            }

            if (isset($group['url']) && $this->isUrlActive($group['url'])) {
                $group['active'] = true;
            }

            foreach ($group['children'] as $childKey => &$child) {
                if (isset($child['resource']) && !$this->_authorization->isAllowed($child['resource'])) {
                    unset($group['children'][$childKey]);
                    continue;
                }

                if (isset($child['url']) && $this->isUrlActive($child['url'])) {
                    $child['active'] = true;
                    $group['active'] = true;
                }
            }
            unset($child);
        }

        return $config;
    }
}
