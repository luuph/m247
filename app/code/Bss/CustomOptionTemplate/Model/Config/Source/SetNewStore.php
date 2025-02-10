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

class SetNewStore implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $collection;

    /**
     * SetNewCustomerGroup constructor.
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $collection
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $collection
    ) {
        $this->collection = $collection;
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
        foreach ($this->collection->toOptionArray() as $store) {
            $data[] = [
                'label' => __($store['label']),
                'value' => __($store['value'])
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
        foreach ($this->collection->toOptionArray() as $store) {
            $data[] = __($store['label']);
        }
        return $data;
    }
}
