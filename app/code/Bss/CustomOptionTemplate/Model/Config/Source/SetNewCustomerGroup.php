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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\Config\Source;

class SetNewCustomerGroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupCollect;

    /**
     * SetNewCustomerGroup constructor.
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollect
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollect
    ) {
        $this->customerGroupCollect = $customerGroupCollect;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];
        $data[] = [
            'label' => 'None',
            'value' => ''
        ];
        foreach ($this->customerGroupCollect->toOptionArray() as $group) {
            $data[] = [
                'label' => __($group['label']),
                'value' => __($group['value'])
            ];
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $data = [];
        $data[] = [
            'label' => 'None',
            'value' => ''
        ];
        foreach ($this->customerGroupCollect->toOptionArray() as $group) {
            $data[] = __($group['label']);
        }
        return $data;
    }
}
