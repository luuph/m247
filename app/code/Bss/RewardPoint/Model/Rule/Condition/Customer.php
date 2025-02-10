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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Rule\Model\Condition\Context;

class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    public const OPTION_EMAIL = 'email';

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AbstractResource|null
     */
    protected $resource;

    /**
     * @var AbstractDb|null
     */
    protected $resourceCollection;

    /**
     * Customer constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->context                      = $context;
        $this->registry                     = $registry;
        $this->_backendData                 = $backendData;
        $this->_assetRepo                   = $assetRepo;
        $this->resource                     = $resource;
        $this->resourceCollection           = $resourceCollection;

        parent::__construct($context, $data);
    }

    /**
     * Load att options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::OPTION_EMAIL => __('Email')
        ];

        $this->setAttributeOption($attributes);

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
        if (!$object->getEntityId()) {
            return true;
        }
        $result = $this->validateCustomer($object);
        return $result;
    }

    /**
     * Validate customer
     *
     * @param \Magento\Framework\DataObject $customer
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateCustomer(\Magento\Framework\DataObject $customer)
    {
        $attrCode = $this->getAttribute();
        $value = $customer->getData($attrCode);
        $res = $this->validateAttribute($value);
        return $res;
    }

    /**
     * Get value after element html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueAfterElementHtml()
    {
        $html = '';
        switch ($this->getAttribute()) {
            case 'email':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Get value element choose url
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        if ($this->getAttribute() == 'email') {
            $url = 'bssreward/rule/chooser/email/' . $this->getAttribute();
            if ($this->getJsFormObject()) {
                $url .= '/form/' . $this->getJsFormObject();
            }
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }
}
