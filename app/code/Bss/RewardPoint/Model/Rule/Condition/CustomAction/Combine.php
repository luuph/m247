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
namespace Bss\RewardPoint\Model\Rule\Condition\CustomAction;

use Magento\Framework\App\Request\Http;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $_conditionAddress;

    /**
     * @var \Bss\RewardPoint\Model\Rule\Condition\ProductFactory
     */
    protected $ruleConditionProductFactory;

    /**
     * @var \Bss\RewardPoint\Model\Rule\Condition\CustomerFactory
     */
    protected $ruleConditionCustomerFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param \Bss\RewardPoint\Model\Rule\Condition\CustomerFactory $ruleConditionCustomerFactory
     * @param \Bss\RewardPoint\Model\Rule\Condition\ProductFactory $ruleConditionProductFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        \Bss\RewardPoint\Model\Rule\Condition\CustomerFactory $ruleConditionCustomerFactory,
        \Bss\RewardPoint\Model\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_conditionAddress = $conditionAddress;
        $this->ruleConditionCustomerFactory = $ruleConditionCustomerFactory;
        $this->ruleConditionProductFactory = $ruleConditionProductFactory;
        $this->request = $request;
        parent::__construct($context, $data);
        $this->setType(\Bss\RewardPoint\Model\Rule\Condition\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNewChildSelectOptions()
    {
        $customerAttributes = $this->ruleConditionCustomerFactory->create()
            ->loadAttributeOptions()->getAttributeOption();
        $customer_attributes = [];
        foreach ($customerAttributes as $code => $label) {
            $customer_attributes[] = [
                'value' => 'Bss\RewardPoint\Model\Rule\Condition\Customer|' . $code,
                'label' => $label,
            ];
        }

        $productAttributes = $this->ruleConditionProductFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Bss\RewardPoint\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => \Bss\RewardPoint\Model\Rule\Condition\CustomAction\Combine::class,
                'label' => __('Conditions Combination'),
            ],
            ['label' => __('Customer'), 'value' => $customer_attributes],
            ['label' => __('Product Attribute'), 'value' => $attributes]
        ]);

        return $conditions;
    }
}
