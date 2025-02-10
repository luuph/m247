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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Model\Config;

use Magento\Framework\Module\Manager as ModuleManager;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    protected $moduleManager;

    protected $_customerGroup;

    /**
     * Group constructor.
     * @param ModuleManager $moduleManager
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     */
    public function __construct(
        ModuleManager $moduleManager,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    ) {
        $this->moduleManager = $moduleManager;
        $this->_customerGroup = $customerGroup;
    }

    /**
     * Get all customer groups
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }

        $customerGroups = $this->_customerGroup->toOptionArray();

        array_unshift($customerGroups, [
                'label' => __('ALL GROUPS'),
                'value' => \Magento\Customer\Api\Data\GroupInterface::CUST_GROUP_ALL,
            ]);

        return $customerGroups;
    }
}
