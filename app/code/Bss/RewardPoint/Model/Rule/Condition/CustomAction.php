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

use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Rule\Model\Condition\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomAction extends \Magento\Rule\Model\Condition\AbstractCondition
{
    public const OPTION_ACTION = 'action';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|null
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|null
     */
    protected $resourceCollection;

    /**
     * @var Context
     */
    protected $context;

    /**
     * CustomAction constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->_backendData = $backendData;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        parent::__construct($context, $data);
    }

    /**
     * Load att option
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::OPTION_ACTION => __('Action')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInputType()
    {
        $type = 'string';

        switch ($this->getAttribute()) {
            case self::OPTION_ACTION:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * Get value element type
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueElementType()
    {
        $type = 'text';

        switch ($this->getAttribute()) {
            case self::OPTION_ACTION:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * Get value select option
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $opt = parent::getValueSelectOptions();

        return array_merge($opt, $this->_prepareValueOptions());
    }

    /**
     * Prepare value options
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareValueOptions()
    {
        $selectOptions = [];

        if ($this->getAttribute() === self::OPTION_ACTION) {
            $selectOptions = [
                [
                    'value' => TransactionActions::REGISTRATION,
                    'label' => __('Registration')
                ],
                [
                    'value' => TransactionActions::SUBSCRIBLE_NEWSLETTERS,
                    'label' => __('Subscrible newsletters')
                ],
                [
                    'value' => TransactionActions::BIRTHDAY,
                    'label' => __('Birthday')],
                [
                    'value' => TransactionActions::FIRST_ORDER,
                    'label' => __('First order')
                ],
                [
                    'value' => TransactionActions::FIRST_REVIEW,
                    'label' => __('First review')
                ],
                [
                    'value' => TransactionActions::REVIEW,
                    'label' => __('Submit Review')
                ],
            ];
        }

        return $selectOptions;
    }

    /**
     * Load operator options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is')
            ]
        );
        return $this;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $attrCode = $this->getAttribute();
        $value = $object->getData($attrCode);
        $result = $this->validateAttribute($value);
        return $result;
    }
}
