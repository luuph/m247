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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Override\Backend\Model\Menu;

class Builder extends \Magento\Backend\Model\Menu\Builder
{
    /**
     * Populate menu object
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu
     * @throws \OutOfRangeException in case given parent id does not exists
     */
    public function getResult(\Magento\Backend\Model\Menu $menu)
    {
        /** @var $items \Magento\Backend\Model\Menu\Item[] */
        $params = [];
        $items = [];

        // Create menu items
        foreach ($this->_commands as $id => $command) {
            $params[$id] = $command->execute();
            $item = $this->_itemFactory->create($params[$id]);
            $items[$id] = $item;
        }

        // Build menu tree based on "parent" param
        foreach ($items as $id => $item) {
            $sortOrder = $this->_getParam($params[$id], 'sortOrder');
            $parentId = $this->_getParam($params[$id], 'parent');
            $isRemoved = isset($params[$id]['removed']);

            if ($isRemoved) {
                continue;
            }
            if (!$parentId) {
                $menu->add($item, null, $sortOrder);
            } else {
                if (!isset($items[$parentId])) {
                    //check if module option core not enable/exist
                    if ($parentId == 'Bss_CustomOptionCore::advanced_custom_option') {
                        continue;
                    }
                    throw new \OutOfRangeException(sprintf('Specified invalid parent id (%s)', $parentId));
                }
                if (isset($params[$parentId]['removed'])) {
                    continue;
                }
                $items[$parentId]->getChildren()->add($item, null, $sortOrder);
            }
        }

        return $menu;
    }
}
