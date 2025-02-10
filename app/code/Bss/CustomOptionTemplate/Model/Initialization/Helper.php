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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\Initialization;

use Bss\CustomOptionTemplate\Helper\HelperController;
use Bss\CustomOptionTemplate\Model\Config;
use Bss\CustomOptionTemplate\Model\Config\Source\SaveMode;
use Bss\CustomOptionTemplate\Model\ResourceModel\Title;
use Bss\CustomOptionTemplate\Model\ResourceModel\Product\Type\Configurable;
use Bss\CustomOptionTemplate\Model\TemplateFactory;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\ValueFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Module\Manager;

class Helper
{
    /**
     * @var Config
     */
    protected $modelConfig;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $deleteoptionIds = [];

    /**
     * @var array
     */
    protected $deleteoptionValueIds = [];

    /**
     * @var array
     */
    protected $isDefaultOptionValues = [];

    /**
     * @var array
     */
    protected $productOptions = [];

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Option\ValueFactory
     */
    protected $optionValue;

    /**
     * @var \Bss\CustomOptionTemplate\Model\OptionFactory
     */
    protected $bssOption;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Option\ValueFactory
     */
    protected $bssOptionValue;

    /**
     * @var \Bss\CustomOptionTemplate\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Title
     */
    protected $optionTitle;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * @var \Bss\CustomOptionTemplate\Helper\HelperController
     */
    protected $helperController;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $format;

    /**
     * @var Configurable
     */
    protected $configurableProductType;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $productOption;

    /**
     * Helper constructor.
     *
     * @param Config $modelConfig
     * @param ProductFactory $productFactory
     * @param OptionFactory $optionFactory
     * @param ValueFactory $optionValue
     * @param \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption
     * @param \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue
     * @param TemplateFactory $templateFactory
     * @param Title $optionTitle
     * @param Product $resourceProduct
     * @param HelperController $helperController
     * @param FormatInterface $format
     * @param Configurable $configurableProductType
     * @param Manager $moduleManager
     * @param Option $productOption
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\Config $modelConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Catalog\Model\Product\Option\ValueFactory $optionValue,
        \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption,
        \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue,
        \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory,
        \Bss\CustomOptionTemplate\Model\ResourceModel\Title $optionTitle,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Bss\CustomOptionTemplate\Helper\HelperController $helperController,
        \Magento\Framework\Locale\FormatInterface $format,
        \Bss\CustomOptionTemplate\Model\ResourceModel\Product\Type\Configurable $configurableProductType,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Product\Option $productOption
    ) {
        $this->modelConfig = $modelConfig;
        $this->productFactory = $productFactory;
        $this->optionFactory = $optionFactory;
        $this->optionValue = $optionValue;
        $this->bssOption = $bssOption;
        $this->bssOptionValue = $bssOptionValue;
        $this->templateFactory = $templateFactory;
        $this->optionTitle = $optionTitle;
        $this->resourceProduct = $resourceProduct;
        $this->helperController = $helperController;
        $this->format = $format;
        $this->configurableProductType = $configurableProductType;
        $this->moduleManager = $moduleManager;
        $this->productOption = $productOption;
    }

    /**
     * @param mixed $option
     * @param int $templateId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveOption($option, $templateId)
    {
        $id = isset($option['option_id']) ? $option['option_id'] : null;
        $visibleCustomerGroup = isset($option['visibility']['customer_group'])
            ? $option['visibility']['customer_group'] : "";
        $visibleStore = isset($option['visibility']['stores']) ? $option['visibility']['stores'] : "";
        $titleStores = isset($option['title_option']) ? $option['title_option'] : "";
        unset($option['values']);
        unset($option['option_id']);
        unset($option['visibility']);
        unset($option['title_option']);
        $json_data = json_encode($option, JSON_FORCE_OBJECT);
        $bssOption = $this->bssOption->create();
        if ($id) {
            $bssOption->load($id);
        }
        $bssOption->setTemplateId($templateId);
        $bssOption->setJsonData($json_data);
        $bssOption->setTitle($titleStores);
        $bssOption->setVisibleForGroupCustomer($visibleCustomerGroup);
        $bssOption->setVisibleForStoreView($visibleStore);
        try {
            $bssOption->save();
            return $bssOption->getId();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param array $optionType
     * @param int $optionId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveOptionType($optionType, $optionId)
    {
        $id = isset($optionType['option_type_id']) ? $optionType['option_type_id'] : null;
        $titleStores = isset($optionType['title_option']) ? $optionType['title_option'] : "";
        $isDefault = isset($optionType['is_default']) ? $optionType['is_default'] : 0;
        unset($optionType['option_type_id']);
        unset($optionType['title_option']);
        unset($optionType['is_default']);
        $json_data = json_encode($optionType, JSON_FORCE_OBJECT);
        $bssOptionValue = $this->bssOptionValue->create();
        if ($id) {
            $bssOptionValue->load($id);
        }
        $bssOptionValue->setOptionId($optionId);
        $bssOptionValue->setJsonData($json_data);
        $bssOptionValue->setTitle($titleStores);
        $bssOptionValue->setIsDefault($isDefault);
        try {
            $bssOptionValue->save();
            $this->isDefaultOptionValues[$bssOptionValue->getId()] = $isDefault;
            return $bssOptionValue->getId();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param array $options
     * @param int $templateId
     * @throws \Exception
     */
    public function saveCustomOptionTemplate(array &$options, $templateId, $productId = null)
    {
        if (!empty($options)) {
            foreach ($options as $key => &$customOptionData) {
                if ($this->checkValidOption($customOptionData)) {
                    unset($options[$key]);
                    continue;
                }
                //check option value valid
                $this->checkValidOptionValue($customOptionData);

                $customOptionId = $this->saveOption($customOptionData, $templateId);
                $options[$key]['template_option_id'] = $customOptionId;
                if (!empty($customOptionData['is_delete'])) {
                    $this->deleteoptionIds[] = $customOptionId;
                }
                if (isset($customOptionData['values'])) {
                    foreach ($customOptionData['values'] as $keyv => $value) {
                        $customOptionTypeId = $this->saveOptionType($value, $customOptionId);
                        $options[$key]['values'][$keyv]['template_option_type_id'] = $customOptionTypeId;
                        if (!empty($value['is_delete'])) {
                            $this->deleteoptionValueIds[] = $customOptionTypeId;
                        }
                    }
                }
            }
            $this->productOptions = $options;
            if ($this->modelConfig->getConfigSaveMode() == SaveMode::UPDATE_ON_SAVE) {
                $this->saveCustomOptionforProduct($templateId, $productId);
            }
            return true;
        }

        return false;
    }

    /**
     * Check option value valid
     * @param array $customOptionData
     */
    protected function checkValidOptionValue(&$customOptionData)
    {
        if (isset($customOptionData['values'])) {
            foreach ($customOptionData['values'] as $key => &$value) {
                if (isset($value['price'])) {
                    $value['price'] = $this->format->getNumber($value['price']);
                }
                if ($value['title'] == '') {
                    unset($customOptionData['values'][$key]);
                }
            }
        }
    }

    /**
     * @param array $customOptionData
     * @return bool
     */
    protected function checkValidOption($customOptionData)
    {
        if ($customOptionData['title'] == '' || $customOptionData['type'] == '') {
            return true;
        }
        return false;
    }

    /**
     * Delete option old product assgin
     *
     * @param array $productIdsDelete
     * @param int $templateId
     * @param array|null $options
     * @throws LocalizedException
     */
    public function deleteOptionOldProductAssign(array $productIdsDelete, $templateId, $options = null)
    {
        try {
            if (!empty($productIdsDelete)) {
                $bssOptionModel = $this->bssOption->create();
                $optionData = $options ? $options : $this->productOptions;
                foreach ($productIdsDelete as $productId) {
                    if ($productId > 0) {
                        $bssOptionModel->removeTemplateId($productId, $templateId);
                        foreach ($optionData as $customOptionData) {
                            $optionId = $bssOptionModel->getBaseOptionId(
                                $productId,
                                $customOptionData['template_option_id']
                            ) ?: null;
                            $this->deleteOldBaseOption($optionId);
                        }
                    }

                    //Remove has_option, required_options of product
                    $this->saveHasOptionAndRequire($productId);
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param mixed $option
     * @param int $optionId
     * @throws \Exception
     */
    private function deleteBaseOption($option, $optionId)
    {
        if (!empty($option['is_delete']) && $optionId) {
            $this->optionFactory->create()->load($optionId)->delete();
        }
    }

    /**
     * @param int $optionId
     * @throws \Exception
     */
    private function deleteOldBaseOption($optionId)
    {
        if ($optionId) {
            $this->optionFactory->create()->load($optionId)->delete();
        }
    }

    /**
     * @param array $value
     * @param int $optionTypeId
     * @throws \Exception
     */
    private function deleteBaseOptionType($value, $optionTypeId)
    {
        if (!empty($value['is_delete']) && $optionTypeId) {
            $this->optionValue->create()->load($optionTypeId)->delete();
        }
    }

    /**
     * @param mixed $product
     * @param int $templateId
     * @return string
     */
    protected function setTemplateInExclude($product, $templateId, $type = 'tenplates_included')
    {
        $templateIncluded =  $product->getData($type);
        if ($templateIncluded) {
            $templateIncluded = explode(",", $templateIncluded);
            if (!in_array($templateId, $templateIncluded)) {
                $templateIncluded[] = $templateId;
            }
            return implode(",", $templateIncluded);
        }
        return $templateId;
    }

    /**
     * Fill Product Options
     *
     * @param int $productId
     * @param int $templateId
     * @param array $productSkipIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function fillProductOptions($productId, $templateId, $productSkipIds = [])
    {
        $product = $this->productFactory->create()->load($productId)->setStoreId(0);
        if ($product->getOptionsReadonly() || empty($this->productOptions)) {
            return [];
        }
        //save template include/exclude for product
        $product->setData('tenplates_included', $this->setTemplateInExclude($product, $templateId));
        $this->resourceProduct->saveAttribute($product, 'tenplates_included');
        $excludeList = $product->getData('tenplates_excluded') ?
            explode(',', $product->getData('tenplates_excluded'))
            : [];

        if ($product->getTypeId() == "grouped"
            || ($product->getTypeId() == "bundle" && $product->getPriceType() == 0)
            || in_array($templateId, $excludeList)
            || in_array($productId, $productSkipIds)
        ) {
            $product->setData('tenplates_excluded', $this->setTemplateInExclude($product, $templateId, 'tenplates_excluded'));
            $this->resourceProduct->saveAttribute($product, 'tenplates_excluded');
            return [];
        }

        $customOptionIds = $customOptVisibleCustomer = $customOptVisibleStore = [];
        $customOptionTitles = [];
        $customOptions = [];
        $product->setCanSaveCustomOptions(true);

        foreach ($this->productOptions as $customOptionData) {
            $optionId = $this->bssOption->create()->getBaseOptionId(
                $productId,
                $customOptionData['template_option_id']
            ) ?: null;
            $customOptionData['option_id'] = $optionId;
            $customOptionData['is_use_default'] = 1;
            $customOptionData['is_delete_store_title'] = 1;
            $this->deleteBaseOption($customOptionData, $optionId);

            if (!empty($customOptionData['is_delete'])
                || ($product->getTypeId() == 'configurable' && isset($customOptionData['price_type'])
                    && $customOptionData['price_type'] == 'percent')
            ) {
                continue;
            }

            if (isset($customOptionData['price'])) {
                $customOptionData['price'] = $this->format->getNumber($customOptionData['price']);
            }

            if (isset($customOptionData['values'])) {
                foreach ($customOptionData['values'] as $key => $value) {
                    $optionTypeId = $this->bssOptionValue->create()->getBaseOptionTypeId(
                        $optionId,
                        $value['template_option_type_id']
                    ) ?: null;
                    $customOptionData['values'][$key]['option_type_id'] = $optionTypeId;
                    $customOptionData['values'][$key]['is_use_default'] = 1;
                    $customOptionData['values'][$key]['is_delete_store_title'] = 1;
                    if (!empty($value['is_delete']) && $optionTypeId) {
                        $this->deleteBaseOptionType($value, $optionTypeId);
                        continue;
                    }
                    if ($product->getTypeId() == 'configurable' && $value['price_type'] == 'percent') {
                        continue 2;
                    }
                }
            }
            $customOption = $this->saveCustomOption($product, $customOptionData);
            $customOptionIds[] = $customOption->getId();
            $customOptVisibleCustomer[] = $this->helperController->setVisibleOptionByCustomer(
                $customOptionData,
                $customOption->getId()
            );
            $customOptVisibleStore[] = $this->helperController->setVisibleOptionByStore(
                $customOptionData,
                $customOption->getId()
            );
            $customOptionTitles[$customOption->getId()] = $this->helperController->setTitleForStores(
                $customOptionData
            );
            $product->addOption($customOption);
            $customOptions[] = $customOption;
        }

        /* Fix bug set title for other store view when save custom option template */
        foreach ($customOptions as $customOption) {
            $this->deleteAndAddTitles($customOption);
        }

        // add product has option and require
        $this->saveHasOptionAndRequire($productId, $product);

        // add option visible
        $this->optionTitle->addVisibleOptions($customOptVisibleCustomer, 'customer');
        $this->optionTitle->addVisibleOptions($customOptVisibleStore, 'store');

        //add title option for store
        $this->optionTitle->addTitleForStores($customOptionTitles);
        return $customOptionIds;
    }

    /**
     * @param mixed $product
     * @param array $customOptionData
     * @return \Magento\Catalog\Model\Product\Option
     * @throws \Exception
     */
    private function saveCustomOption($product, $customOptionData)
    {
        $customOption = $this->optionFactory->create();
        $id = $product->getRowId() ?: $product->getId();
        $customOption->setProductId($id)->addData($customOptionData);

        try {
            $customOption->setBssDcoRequire(0);
            if (isset($customOptionData['is_require']) && $customOptionData['is_require'] == 1) {
                $customOption->setBssDcoRequire(1);
            }
            $customOption->setData("bsscustomoption_template_save", 1);
            $customOption->save();
            return $customOption;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Delete Option Titles
     *
     * @param \Magento\Catalog\Model\Product\Option $customOption
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteAndAddTitles($customOption)
    {
        try {
            $optionId = $customOption->getOptionId();
            if ($optionId) {
                $this->optionTitle->deleteOptionTitles($optionId);
            }
            $valuesCollection = $customOption->getValuesCollection();
            if ($valuesCollection->getSize() > 0) {
                $valuesData = $valueOptionTitles =  [];
                foreach ($valuesCollection as $value) {
                    $optionTypeId = $value->getData('option_type_id');
                    $optionTemplateValueId = $value->getData('template_option_type_id');
                    if (isset($this->isDefaultOptionValues[$optionTemplateValueId])) {
                        $valuesData[] = $this->helperController->setIsDefaultValues(
                            $this->isDefaultOptionValues[$optionTemplateValueId],
                            $optionTypeId
                        );
                    }
                    $valueOptionTitles[$value->getId()] = $this->helperController->setTitleValuesForStores(
                        $value->getData('title_option'),
                        $value->getTitle()
                    );
                    if ($optionTypeId) {
                        $this->optionTitle->deleteOptionValueTitles($optionTypeId);
                    }
                }
                //add is default value
                $this->optionTitle->addIsDefaultValue($valuesData);
                //add title value option for store
                $this->optionTitle->addTitleForStores($valueOptionTitles, 'option_type_id');
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     *
     */
    private function deleteOption()
    {
        $bssOption = $this->bssOption->create();
        $collection = $bssOption->getCollection();
        if (!empty($this->deleteoptionIds)) {
            $collection->addFieldToFilter('option_id', $this->deleteoptionIds);
            $collection->walk('delete');
        }
    }

    /**
     *
     */
    private function deleteOptionType()
    {
        $bssOptionValue = $this->bssOptionValue->create();
        $collection = $bssOptionValue->getCollection();
        if (!empty($this->deleteoptionValueIds)) {
            $collection->addFieldToFilter('option_type_id', $this->deleteoptionValueIds);
            $collection->walk('delete');
        }
    }

    /**
     * @param int $templateId
     * @throws \Exception
     */
    public function saveCustomOptionforProduct($templateId, $productId = null)
    {
        $template = $this->templateFactory->create()->load($templateId);
        if ($template->getIsActive()) {
            if ($template->getProductIds() || $productId) {
                $productIds = $productId ? [$productId] : explode(',', $template->getProductIds());
                $productSkipIds = [];

                //Skip add CO in child product if not install Bss_SDCP.
                if (!$this->moduleManager->isEnabled('Bss_Simpledetailconfigurable')) {
                    $parentIds = $this->configurableProductType->getParentIdsByChild($productIds);
                    $productSkipIds = array_keys($parentIds);
                }

                foreach ($productIds as $id) {
                    $this->fillProductOptions($id, $templateId, $productSkipIds);
                }
            }
        } else {
            if ($template->getProductIds()) {
                $bssOptionModel = $this->bssOption->create();
                $productIds = explode(',', $template->getProductIds());
                foreach ($productIds as $id) {
                    $bssOptionModel->removeTemplateId($id, $templateId);
                }
            }
            //delete option of product
            $this->deleteBaseOptionProduct($templateId);
        }
        $this->deleteOption();
        $this->deleteOptionType();
    }
    /**
     * @param int $templateId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteBaseOptionProduct($templateId)
    {
        $optionIds= [];
        $options = $this->bssOption->create()->getCollection();
        $options->addFieldToFilter('template_id', $templateId);
        if ($options->getSize() > 0) {
            foreach ($options as $option) {
                $optionIds[] = $option->getId();
                $this->deleteBaseOptionTypeProduct($option->getId());
            }
        }
        if (!empty($optionIds)) {
            $collection = $this->optionFactory->create()->getCollection();
            $collection->addFieldToFilter('template_option_id', ['in' => $optionIds]);
            try {
                $collection->walk('delete');
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            }
        }
    }

    /**
     * @param int $optionId
     * @throws \Exception
     */
    private function deleteBaseOptionTypeProduct($optionId)
    {
        $optionTypeIds= [];
        $optionTypes = $this->bssOptionValue->create()->getCollection();
        $optionTypes->addFieldToFilter('option_id', $optionId);
        if ($optionTypes->getSize() > 0) {
            foreach ($optionTypes as $value) {
                $optionTypeIds[] = $value->getId();
            }
        }
        if (!empty($optionTypeIds)) {
            $collection = $this->optionValue->create()->getCollection();
            $collection->addFieldToFilter('template_option_type_id', ['in' => $optionTypeIds]);
            try {
                $collection->walk('delete');
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            }
        }
    }

    /**
     * Set product options
     *
     * @param $productOptions
     * @return void
     */
    public function setProductOptions($productOptions)
    {
        $this->productOptions = $productOptions;
    }

    /**
     * Set options delete
     *
     * @param $optionsDelete
     * @return void
     */
    public function setOptionsDelete($optionsDelete)
    {
        $this->deleteoptionValueIds = $optionsDelete;
    }

    /**
     * Get options save
     *
     * @return array
     */
    public function getOptionsSave()
    {
        return $this->productOptions;
    }

    /**
     * Get options delete
     *
     * @return array
     */
    public function getOptionsDelete()
    {
        return $this->deleteoptionValueIds;
    }

    /**
     * Check & Save multi product option data.
     *
     * @param int $productId
     * @param mixed|null $product
     * @return void
     */
    public function saveHasOptionAndRequire($productId, $product = null)
    {
        if (!$product) {
            $product = $this->productFactory->create()->load($productId)->setStoreId(0);
        }
        $customOptions = $this->productOption->getProductOptionCollection($product);

        $optionData = [
            'has_options' => 0,
            'required_options' => 0
        ];

        if ($data = $customOptions->getData()) {
            $optionData['has_options'] = 1;
            foreach ($data as $option) {
                if (!empty($option['is_require'])) {
                    $optionData['required_options'] = 1;
                    break;
                }
            }
        }

        $this->optionTitle->addHasOptionAndRequire($productId, $optionData);
    }
}
