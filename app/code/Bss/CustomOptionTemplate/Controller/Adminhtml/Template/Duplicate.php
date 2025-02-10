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
namespace Bss\CustomOptionTemplate\Controller\Adminhtml\Template;

use Bss\CustomOptionTemplate\Model\Option\ValueFactory;
use Bss\CustomOptionTemplate\Model\OptionFactory;
use Bss\CustomOptionTemplate\Model\TemplateFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 *
 */
class Duplicate extends \Magento\Backend\App\Action
{
    /**
     * @var array
     */
    protected $dependentIds = [];

    /**
     * @var Json
     */
    protected $json;

    /**
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomOptionTemplate::grid';

    /**
     * @var \Bss\CustomOptionTemplate\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\OptionFactory
     */
    protected $bssOption;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Option\ValueFactory
     */
    protected $bssOptionValue;


    /**
     * Duplicate constructor.
     * @param Json $json
     * @param Context $context
     * @param TemplateFactory $templateFactory
     * @param OptionFactory $bssOption
     * @param ValueFactory $bssOptionValue
     */
    public function __construct(
        Json $json,
        \Magento\Backend\App\Action\Context $context,
        \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory,
        \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption,
        \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue,
    ) {
        $this->json = $json;
        parent::__construct($context);
        $this->templateFactory = $templateFactory;
        $this->bssOption = $bssOption;
        $this->bssOptionValue = $bssOptionValue;
    }
    /**
     * Create Duplicate Template
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $templateId = $this->getRequest()->getParam('template_id', null);
        $template = $this->templateFactory->create()->load($templateId);
        if ($template->getId()) {
            try {
                $this->handleDependentOption($template);
                $data = $template->getData();
                unset($data['template_id']);
                unset($data['product_ids']);
                unset($data['skus']);

                $dupTemplate = $this->templateFactory->create();
                $dupTemplate->setData($data);
                $dupTemplate->save();
                $dupTemplateId = $dupTemplate->getId();
                $this->dupOptions($templateId, $dupTemplateId);
                $this->messageManager->addSuccessMessage(__('You duplicated success.'));
                $resultRedirect->setPath('*/*/edit', ['_current' => true, 'template_id' => $dupTemplate->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect;
    }

    /**
     * Set dependent_id, dependent_value all option jsonData of template
     *
     * @param $template
     * @return void
     */
    public function handleDependentOption($template)
    {
        if (!$template->getOptionsData()) {
            return;
        }
        $optionsData = $this->json->unserialize($template->getOptionsData());
        $dependentIds = [];
        foreach ($optionsData as $optionData) {
            $dependentIds[$optionData["dependent_id"]] = $optionData["dependent_id"];
            if (isset($optionData["values"])) {
                foreach ($optionData["values"] as $optionChild) {
                    $dependentIds[$optionChild["dependent_id"]] = $optionChild["dependent_id"];
                }
            }
        }
        asort($dependentIds);
        $dependentCurrent = time() * 1000;
        foreach ($dependentIds as $key => $dependentId) {
            $dependentIds[$key] = $dependentCurrent++;
        }
        $this->dependentIds = $dependentIds;
        foreach ($optionsData as &$optionData) {
            $optionData["dependent_id"] = $dependentIds[$optionData["dependent_id"]];
            $this->handleDependentValue($optionData);
            if (isset($optionData["values"])) {
                foreach ($optionData["values"] as &$optionChild) {
                    $optionChild["dependent_id"] = $dependentIds[$optionChild["dependent_id"]];
                    $this->handleDependentValue($optionChild);
                }
            }
        }
        $template->setOptionsData($this->json->serialize($optionsData));
    }

    /**
     * @param int $templateId
     * @param mixed $jsonData
     * @param int $optionId
     * @return mixed
     * @throws \Exception
     */
    private function saveOption($templateId, $jsonData, $optionId)
    {
        $dupOption = $this->bssOption->create();
        $dupOption->setTemplateId($templateId);
        $dupOption->setJsonData($jsonData);

        $template = $this->templateFactory->create()->load($templateId);
        $data = json_decode($template->getData('options_data'), true);
        $optionData = $this->json->unserialize($jsonData);
        // check if $options_data starts with 1 or $optionId because first time save a template, $optionIds always start with 1
        if (array_key_exists($optionId, $data)) {
            $dupOption->setVisibleForGroupCustomer($data[$optionId]['visibility']['customer_group']);
            $dupOption->setVisibleForStoreView($data[$optionId]['visibility']['stores']);
            $dupOption->setTitle($data[$optionId]['title_option']);
        } else {
            if (isset($data[$optionData["id"]])) {
                $id = $optionData["id"];
                $dupOption->setVisibleForGroupCustomer($data[$id]['visibility']['customer_group']);
                $dupOption->setVisibleForStoreView($data[$id]['visibility']['stores']);
                $dupOption->setTitle($data[$id]['title_option']);
            }
        }

        try {
            $dupOption->save($dupOption);
            return $dupOption->getId();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Set dependent_id, dependent_value all option when create option eav ..
     *
     * @param $jsonOptionData
     * @return bool|string
     */
    public function handleDependentOptionChild($jsonOptionData)
    {
        $optionData = $this->json->unserialize($jsonOptionData);
        $optionData["dependent_id"] = $this->dependentIds[$optionData["dependent_id"]];
        if (isset($optionData["values"])) {
            foreach ($optionData["values"] as &$optionChild) {
                $optionChild["dependent_id"] = $this->dependentIds[$optionChild["dependent_id"]];
                $this->handleDependentValue($optionChild);
            }
        }
        return $this->json->serialize($optionData);
    }

    /**
     * @param int $templateId
     * @param mixed $dupTemplateId
     */
    private function dupOptions($templateId, $dupTemplateId)
    {
        $collection = $this->bssOption->create()->getCollection();
        $collection->addFieldToFilter('template_id', $templateId);
        if ($collection->getSize() > 0) {
            foreach ($collection as $option) {
                $dupOptionId = $this->saveOption(
                    $dupTemplateId,
                    $this->handleDependentOptionChild($option->getJsonData()),
                    $option->getId()
                );
                $this->dupOptionTypes($option->getId(), $dupOptionId);
            }
        }

    }

    /**
     * @param int $optionId
     * @param mixed $jsonData
     * @param bool $isDefault
     * @param string $title
     * @throws \Exception
     */
    private function saveOptionType($optionId, $jsonData, $isDefault = false, $title = "")
    {
        $dupOptionType = $this->bssOptionValue->create();
        $dupOptionType->setOptionId($optionId);
        $dupOptionType->setJsonData($jsonData);
        $dupOptionType->setIsDefault($isDefault);
        $dupOptionType->setTitle($title);
        try {
            $dupOptionType->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param int $optionId
     * @param mixed $dupOptionId
     */
    private function dupOptionTypes($optionId, $dupOptionId)
    {
        $collection =  $this->bssOptionValue->create()->getCollection();
        $collection->addFieldToFilter('option_id', $optionId);
        if ($collection->getSize() > 0) {
            foreach ($collection as $value) {
                $jsonData = $this->handleDependentOptionTypes($value->getJsonData());
                $isDefault = $value->getIsDefault();
                $title = $value->getTitle();
                $this->saveOptionType($dupOptionId, $jsonData, $isDefault, $title);
            }
        }
    }

    /**
     * Set dependent_id, dependent_value all option child when create option eav ...
     *
     * @param $jsonOptionData
     * @return bool|string
     */
    public function handleDependentOptionTypes($jsonOptionData)
    {
        $optionChild = $this->json->unserialize($jsonOptionData);
        $optionChild["dependent_id"] = $this->dependentIds[$optionChild["dependent_id"]];
        $this->handleDependentValue($optionChild);
        return $this->json->serialize($optionChild);
    }

    /**
     * Handle dependent value
     *
     * @param array $optionData
     * @return void
     */
    public function handleDependentValue(&$optionData)
    {
        if (isset($optionData["depend_value"])) {
            $dependValues = explode(",", $optionData["depend_value"]);
            foreach ($dependValues as &$dependValue) {
                $dependValue = $this->dependentIds[$dependValue];
            }
            $optionData["depend_value"] = implode(",", $dependValues);
        }
    }
}
