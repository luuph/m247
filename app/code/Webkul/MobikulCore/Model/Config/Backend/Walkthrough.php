<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Config\Backend;
 
/**
 * Class Walkthrough model
 */
class Walkthrough extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry, 
            $config, 
            $cacheTypeList, 
            $resource,
            $resourceCollection,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * BeforeSave Function
     */
    public function beforeSave()
    {
        $label = $this->getData('field_config/label');
        $previousValue = $this->helper->getConfigData("mobikul/walkthrough/walkthrough_version");

        if ($this->getValue() == '') {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is required.'));
        } elseif (!is_numeric($this->getValue())) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is not a number.'));
        } elseif ($this->getValue() < 0) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is less than 0.'));
        } elseif ($this->getValue() < $previousValue) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' can not be less than previous.'));
        }

        $this->setValue($this->getValue());

        parent::beforeSave();
    }
}
