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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model\Config\Source;

use Magento\Framework\Module\Manager as ModuleManager;

class CustomerGroups implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroup;

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
        $this->customerGroup = $customerGroup;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }

        $customerGroups = $this->customerGroup->toOptionArray();

        foreach ($customerGroups as $k => $customerGroup) {
            if ($customerGroup['value'] == 0) {
                unset($customerGroups[$k]);
            }
        }

        array_unshift($customerGroups, [
                'label' => __('ALL GROUPS'),
                'value' => \Magento\Customer\Api\Data\GroupInterface::CUST_GROUP_ALL,
            ]);

        return $customerGroups;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $value) {
            $result[$value['value']] =  $value['label'];
        }
        return $result;
    }
}
