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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Block\Adminhtml\Template\Edit\Tab\Options;

use Magento\Backend\Block\Widget;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Option extends Widget
{
    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_values;

    /**
     * @var int
     */
    protected $lastIncrementId = 0;

    /**
     * @var array
     */
    protected $bss_depend_options = [];

    /**
     * @var array
     */
    protected $bss_depend_id = [];

    /**
     * @var int
     */
    protected $_itemCount = 1;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/edit/options/option.phtml';

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $_productOptionConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_configYesNo;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Type
     */
    protected $_optionType;

    /**
     * @var \Bss\CustomOptionTemplate\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Bss\CustomOptionTemplate\Model\CompatibleWithCOAPriceQty
     */
    protected $compatibleWithCOAPriceQty;

    /**
     * Option constructor.
     *
     * @param \Bss\CustomOptionTemplate\Model\CompatibleWithCOAPriceQty $compatibleWithCOAPriceQty
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $configYesNo
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \Bss\CustomOptionTemplate\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\CompatibleWithCOAPriceQty $compatibleWithCOAPriceQty,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $configYesNo,
        \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Bss\CustomOptionTemplate\Model\TemplateFactory  $templateFactory,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \Bss\CustomOptionTemplate\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->compatibleWithCOAPriceQty = $compatibleWithCOAPriceQty;
        $this->_optionType = $optionType;
        $this->_configYesNo = $configYesNo;
        $this->_productOptionConfig = $productOptionConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->templateFactory = $templateFactory;
        $this->helper = $helper;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Bss_CustomOptionTemplate::catalog/product/edit/options/option.phtml');

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'product[options]';
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'product_option';
    }

    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * @return $this
     * @codingStandardsIgnoreStart
     */
    protected function _prepareLayout()
    {
        foreach ($this->_productOptionConfig->getAll() as $option) {
            $this->addChild(
                $option['name'] . '_option_type',
                str_replace(
                    "Magento\\Catalog\\Block\\Adminhtml\\Product",
                    "Bss\\CustomOptionTemplate\\Block\\Adminhtml\\Template",
                    $option['renderer']
                )
            );
        }
        // @codingStandardsIgnoreEnd
        return parent::_prepareLayout();
    }

    /**
     * Default will call html from this function
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddButtonId()
    {
        return $this->getLayout()->getBlock('admin.product.options')->getChildBlock('add_button')->getId();
    }

    /**
     * @return string
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][type]'
        )->setOptions(
            $this->_optionType->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return string
     */
    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => $this->getFieldId() . '_<%- data.id %>_is_require', 'class' => 'select']
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][is_require]'
        )->setOptions(
            $this->_configYesNo->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $canEditPrice = $this->getCanEditPrice();
        $canReadPrice = $this->getCanReadPrice();
        $this->getChildBlock('select_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('file_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('date_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('text_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $templates = $this->getChildHtml(
            'text_option_type'
        ) . "\n" . $this->getChildHtml(
            'file_option_type'
        ) . "\n" . $this->getChildHtml(
            'select_option_type'
        ) . "\n" . $this->getChildHtml(
            'date_option_type'
        );

        return $templates;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptionValues()
    {
        $templateId = $this->getRequest()->getParam('template_id');
        if (!$this->_values || $this->getIgnoreCaching()) {
            $showPrice = $this->getCanReadPrice();
            $values = [];
            $optionMD = $this->helper->getObjectOption();
            $options = $optionMD->getCollection()->addFieldToFilter('template_id', $templateId);
            if ($options->getSize() > 0) {
                $values = $this->returnValuesData($options, $showPrice);
            }
            ksort($values);
            $this->_values = $values;
        }

        return $this->_values;
    }

    /**
     * @param mixed $options
     * @param mixed $showPrice
     * @return array
     */
    protected function returnValuesData($options, $showPrice)
    {
        $values = [];
        foreach ($options as $option) {
            /* @var $option \Magento\Catalog\Model\Product\Option */
            $titleOptionStore = $option->getTitle();
            $option->addData($this->json->unserialize($option->getData('json_data')));
            $this->setItemCount($option->getOptionId());

            $value = [];
            $depend_option = [];

            $value['id'] = $option->getOptionId();
            $value['item_count'] = $this->getItemCount();
            $value['option_id'] = $option->getOptionId();
            $value['title'] = $option->getTitle();
            $value['type'] = $option->getType();
            $value['is_require'] = $option->getIsRequire();
            $value['bss_coap_qty'] = $option->getBssCoapQty();
            $value['bss_description_option_type'] = $option->getBssDescriptionOptionType();
            $value['bss_description_option'] = $option->getBssDescriptionOption();
            $value['sort_order'] = $option->getSortOrder();
            $value['dependent_id'] = (int)$option->getDependentId();
            $value['can_edit_price'] = $this->getCanEditPrice();
            $value['title_option'] = $titleOptionStore;
            $value['visible_for_group_customer'] = $option->getData('visible_for_group_customer');
            $value['visible_for_store_view'] = $option->getData('visible_for_store_view');
            $value['bss_tier_price_option'] = $option->getData('bss_tier_price_option');

            $this->setLastIncrementId((int)$option->getDependentId());

            $depend_option = $this->addDependOptions($depend_option, (int)$option->getDependentId());
            $this->setBssDependIds((int)$option->getDependentId());

            /*Compatible With Bss_CustomOptionAbsolutePriceQuantity*/
            if ($this->helper->isModuleOutputEnabled('Bss_CustomOptionAbsolutePriceQuantity')) {
                if (!$option->getBssCoapQty() && $this->compatibleWithCOAPriceQty->getBssCoapQty($option->getOptionId())) {
                    $value['bss_coap_qty'] = 1;
                }
                if (!$option->getBssDescriptionOptionType()) {
                    $this->compatibleWithCOAPriceQty->checkAndSetValueOption($value, 'bss_custom_option_description_type', 'bss_description_option_type');
                }
                if (!$option->getBssDescriptionOption()) {
                    $this->compatibleWithCOAPriceQty->checkAndSetValueOption($value, 'bss_custom_option_description', 'bss_description_option');
                }
            }
            /*End compatible*/

            if ($this->checkTypeOptionIsSelect($option)) {
                $optionTypeMD = $this->helper->getObjectOptionValues();
                $option_values = $optionTypeMD->getCollection();
                $option_values->addFieldToFilter('option_id', $option->getOptionId());
                $i = 0;
                $itemCount = 0;
                foreach ($option_values as $_value) {
                        $titleOptionValueStore = $_value->getTitle();
                    $_value->addData($this->json->unserialize($_value->getData('json_data')));
                    /*Compatible With Bss_CustomOptionAbsolutePriceQuantity*/
                    if ($this->helper->isModuleOutputEnabled('Bss_CustomOptionAbsolutePriceQuantity')) {
                        if (!$_value->getData('price') || !$_value->getData('price_type')) {
                            $this->compatibleWithCOAPriceQty->checkAndSetAbsolutePriceOptionSelect($_value);
                        }
                        if (!$_value->getBssTierPriceOption()) {
                            $this->compatibleWithCOAPriceQty->checkAndSetTierPriceOptionSelect($_value);
                        }
                    }
                    /*End compatible*/
                    /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
                    $value['optionValues'][$i] = [
                        'item_count' => max($itemCount, $_value->getOptionTypeId()),
                        'option_id' => $option->getOptionId(),
                        'option_type_id' => $_value->getOptionTypeId(),
                        'title' => $_value->getTitle(),
                        'price' => $showPrice ? $this->getPriceValue(
                            $_value->getPrice(),
                            $_value->getPriceType()
                        ) : '',
                        'price_type' => $showPrice ? $_value->getPriceType() : 0,
                        'sku' => $_value->getSku(),
                        'sort_order' => $_value->getSortOrder(),
                        'dependent_id' => (int)$_value->getDependentId(),
                        'depend_value' => $_value->getDependValue(),
                        'image_url' => $_value->getImageUrl(),
                        'is_default' => $_value->getIsDefault(),
                        'title_option' => $titleOptionValueStore,
                        'swatch_image_url' => $_value->getSwatchImageUrl(),
                        'bss_tier_price_option' => $_value->getBssTierPriceOption()
                    ];

                    $depend_option = $this->addDependOptions($depend_option, (int)$_value->getDependentId());
                    $this->setBssDependIds((int)$_value->getDependentId());
                    $this->setLastIncrementId((int)$_value->getDependentId());
                    $i++;
                }
                if (isset($value['optionValues']) && !empty($value['optionValues'])) {
                    usort($value['optionValues'], function ($a, $b) {
                        return $a['sort_order'] - $b['sort_order'];
                    });
                }
            } else {
                /*Compatible With Bss_CustomOptionAbsolutePriceQuantity*/
                if ($this->helper->isModuleOutputEnabled('Bss_CustomOptionAbsolutePriceQuantity')) {
                    if (!$option->getData('price') || !$option->getData('price_type')) {
                        $this->compatibleWithCOAPriceQty->checkAndSetAbsolutePriceOption($option);
                    }
                    if (!$option->getData('bss_tier_price_option')) {
                        $this->compatibleWithCOAPriceQty->checkAndSetValueOption($value, 'bss_tier_price_product_option', 'tier_price');
                    }
                }
                /*End compatible*/
                $value['price'] = $showPrice ? $this->getPriceValue(
                    $option->getPrice(),
                    $option->getPriceType()
                ) : '';
                $value['price_type'] = $option->getPriceType();
                $value['sku'] = $option->getSku();
                $value['max_characters'] = $option->getMaxCharacters();
                $value['file_extension'] = $option->getFileExtension();
                $value['image_size_x'] = $option->getImageSizeX();
                $value['image_size_y'] = $option->getImageSizeY();
            }

            $this->bss_depend_options[$option->getOptionId()] = $depend_option;
            $values[$option->getSortOrder()] = $this->dataObjectFactory->create()->addData($value);
        }
        return $values;
    }

    /**
     * @param mixed $option
     * @return bool
     */
    private function checkTypeOptionIsSelect($option)
    {
        return $option->getType() == 'drop_down'
            || $option->getType() == 'radio'
            || $option->getType() =='checkbox'
            || $option->getType() =='multiple';
    }

    /**
     * @param int $incrementId
     * @return int|int
     */
    public function setLastIncrementId(int $incrementId)
    {
        if ($this->lastIncrementId < $incrementId) {
            $this->lastIncrementId = $incrementId;
        }
        return $this->lastIncrementId;
    }

    /**
     * @param int $dependentId
     * @return array
     */
    public function setBssDependIds($dependentId)
    {
        if ((int)$dependentId > 0) {
            $this->bss_depend_id[$dependentId] = true;
        }
        return $this->bss_depend_id;
    }

    /**
     * @param array $depend_option
     * @param int $dependentId
     * @return array
     */
    public function addDependOptions($depend_option, $dependentId)
    {
        if ((int)$dependentId > 0) {
            $depend_option[$dependentId] = true;
        }
        return $depend_option;
    }

    /**
     * @return int
     */
    public function getLastIncrementId()
    {
        return $this->lastIncrementId;
    }

    /**
     * @return array
     */
    public function getBssDependIds()
    {
        return $this->bss_depend_id;
    }

    /**
     * @return array
     */
    public function getBssDependOptions()
    {
        return $this->bss_depend_options;
    }

    /**
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        if (!$value) {
            return '';
        }

        if ($type == 'percent' || $type == 'fixed' || $type == 'abs') {
            return number_format($value, 2, null, '');
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isCompatibleAbsolutePriceQuantity()
    {
        return $this->helper->isCompatibleAbsolutePriceQuantity();
    }

    /**
     * @return bool
     */
    public function isCompatibleCOImage()
    {
        return $this->helper->isCompatibleCOImage();
    }

    /**
     * @return bool
     */
    public function isCompatibleDependentCO()
    {
        return $this->helper->isCompatibleDependentCO();
    }

    /**
     * @return bool|false|string
     */
    public function getListWebsitesArray()
    {
        return $this->json->serialize($this->helper->getWebsitesArray());
    }

    /**
     * @return bool|false|string
     */
    public function getCustomerGroupsArray()
    {
        return $this->json->serialize($this->helper->getCustomerGroupsArray());
    }

    /**
     * @return mixed
     */
    public function getCurrencySymbol()
    {
        return $this->helper->getCurrencySymbol();
    }

    /**
     * @return array
     */
    public function getListStoreView()
    {
        return $this->helper->getListStoreView();
    }

    /**
     * @return array
     */
    public function getListCustomerGroupArray()
    {
        return $this->helper->getListCustomerGroupArray();
    }

    /**
     * @param mixed $data
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function serializeJson($data)
    {
        return $this->json->serialize($data);
    }
}
