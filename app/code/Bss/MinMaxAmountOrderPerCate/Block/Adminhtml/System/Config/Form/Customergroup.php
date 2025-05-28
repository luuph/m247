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
 * @package    Bss_MinMaxAmountOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxAmountOrderPerCate\Block\Adminhtml\System\Config\Form;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Customer\Model\GroupFactory;

class Customergroup extends Select
{
    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * Customergroup constructor.
     * @param Context $context
     * @param GroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        GroupFactory $groupFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->getOptions()) {
            $customerGroupCollection = $this->groupFactory->create()->getCollection();
            $cOptions[] = [
                'label' => $this->escapeHtml('Please Select a Customer Group'),
                'value' => ''
            ];
            foreach ($customerGroupCollection as $customerGroup) {
                 $cOptions[] = [
                     'label' => $this->escapeHtml($customerGroup->getCustomerGroupCode()),
                     'value' => $customerGroup->getCustomerGroupId()
                 ];
            }
        }
        return $cOptions;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $options =  $this->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }
        return parent::_toHtml();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
