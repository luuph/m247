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
namespace Bss\RewardPoint\Model\Rule\Condition;

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
     * @var CustomActionFactory
     */
    protected $ruleConditionCustomActionFactory;

    /**
     * @var ProductFactory
     */
    protected $ruleConditionProductFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var CustomerFactory
     */
    protected $ruleConditionCustomerFactory;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param CustomActionFactory $ruleConditionCustomActionFactory
     * @param CustomerFactory $ruleConditionCustomerFactory
     * @param ProductFactory $ruleConditionProductFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        \Bss\RewardPoint\Model\Rule\Condition\CustomActionFactory $ruleConditionCustomActionFactory,
        \Bss\RewardPoint\Model\Rule\Condition\CustomerFactory $ruleConditionCustomerFactory,
        \Bss\RewardPoint\Model\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_eventManager = $eventManager;
        $this->_conditionAddress = $conditionAddress;
        $this->ruleConditionCustomActionFactory = $ruleConditionCustomActionFactory;
        $this->ruleConditionCustomerFactory = $ruleConditionCustomerFactory;
        $this->ruleConditionProductFactory = $ruleConditionProductFactory;
        $this->request = $request;
        parent::__construct($context, $data);
        $this->setType(\Bss\RewardPoint\Model\Rule\Condition\Combine::class);
    }

    /**
     * Get new child select option
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNewChildSelectOptions()
    {
        if ($this->getRule()->getType()) {
            $type = $this->getRule()->getType();
        } else {
            $type = $this->request->getParam('type');
        }

        if ($type == 'cart') {
            return $this->_getCartConditions();
        } else {
            $itemAttributes = $this->_getCustomActionAttributes();
            $attributes = $this->convertToAttributes($itemAttributes, 'customAction', 'Action of customer');
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => \Bss\RewardPoint\Model\Rule\Condition\CustomAction\Combine::class,
                'label' => __('Conditions Combination'),
            ],
        ]);

        foreach ($attributes as $group => $arrAttributes) {
            $conditions = array_merge_recursive($conditions, [
                [
                    'label' => $group,
                    'value' => $arrAttributes,
                ],
            ]);
        }

        return $conditions;
    }

    /**
     * Get cart condition
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCartConditions()
    {
        $addressAttributes = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                'label' => __('Product attribute combination')
            ],
            [
                'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class,
                'label' => __('Products subselection')
            ],
            [
                'value' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                'label' => __('Conditions combination')
            ],
            [
                'label' => __('Cart Attribute'),
                'value' => $attributes
            ],
        ]);

        $additional = $this->dataObjectFactory->create();
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    /**
     * Get custom action att
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCustomActionAttributes()
    {
        $customActionCondition = $this->ruleConditionCustomActionFactory->create();
        $customActionAttributes = $customActionCondition->loadAttributeOptions()->getAttributeOption();

        return $customActionAttributes;
    }

    /**
     * Convert to att
     *
     * @param array $itemAttributes
     * @param string $condition
     * @param string $group
     * @return array
     */
    protected function convertToAttributes($itemAttributes, $condition, $group)
    {
        $attributes = [];
        foreach ($itemAttributes as $code => $label) {
            $attributes[$group][] = [
                'value' => '\\Bss\\RewardPoint\\Model\\Rule\\Condition\\' . ucfirst($condition) . '|' . $code,
                'label' => $label,
            ];
        }

        return $attributes;
    }
}
