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
namespace Bss\CustomOptionTemplate\Plugin;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\NoSuchEntityException;

class OptionPlugin
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\CustomOptionTemplate\Model\OptionFactory
     */
    protected $optionModelFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Option
     */
    protected $optionResource;

    /**
     * OptionPlugin constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\CustomOptionTemplate\Model\OptionFactory $optionModelFactory
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\Option $optionResource
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\CustomOptionTemplate\Model\OptionFactory $optionModelFactory,
        \Bss\CustomOptionTemplate\Model\ResourceModel\Option $optionResource
    ) {
        $this->json = $json;
        $this->productRepository = $productRepository;
        $this->optionModelFactory = $optionModelFactory;
        $this->optionResource = $optionResource;
    }

    /**
     * Set exclude template
     * @param Option $subject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSave(
        \Magento\Catalog\Model\Product\Option $subject
    ) {
        try {
            $product = $this->productRepository->getById($subject->getProductId());
        } catch (NoSuchEntityException $ex) {
            $product = null;
        }
        if ($product && $product->getData('tenplates_excluded')) {
            $templateExcludes = explode(",", $product->getData('tenplates_excluded'));
            $templateId = $this->optionResource->getTemplateIdFromTemplateOptionId(
                $subject->getData('template_option_id')
            );
            if (in_array($templateId, $templateExcludes)) {
                $subject->setData('template_option_id', 0);
            }
        }
    }

    /**
     * Set visible for Customer and Store
     *
     * @param Option $subject
     * @param Option $result
     * @return Option
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave(
        \Magento\Catalog\Model\Product\Option $subject,
        $result
    ) {
        if ($subject->getData('option_visibility')) {
            $data = $this->json->unserialize($subject->getData('option_visibility'));
            // add visible option
            $this->optionResource->addVisibleOptions(
                $subject->getOptionId(),
                ['visible_for_group_customer' => $data['visible_for_group_customer']],
                $type = 'customer'
            );
            $this->optionResource->addVisibleOptions(
                $subject->getOptionId(),
                ['visible_for_store_view' => $data['visible_for_store_view']],
                $type = 'store'
            );
        }
        return $result;
    }
    /**
     * AroundGetData
     *
     * @param Option $subject
     * @param mixed $result
     * @param string $key
     * @param string $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        Option $subject,
        $result,
        $key = '',
        $index = null
    ) {
        $templateId = 0;
        if ($key === '') {
            if (isset($result['template_option_id']) && $result['template_option_id'] != 0) {
                $templateId = $this->optionResource->getTemplateIdFromTemplateOptionId($result['template_option_id']);
            }
            $data = [ 'option_id' => $result['option_id'], 'template_option_id' => $templateId];
            $data = $this->json->serialize($data);
            $result['check_bss_template_data'] = $data;
            $visibleData['visible_for_group_customer'] = $this->optionResource->getVisibleCustomer(
                $subject->getOptionId()
            );
            $visibleData['visible_for_store_view'] = $this->optionResource->getVisibleStore($subject->getOptionId());
            $result['option_visibility'] = $this->json->serialize($visibleData);

        }
        if (($key === 'check_bss_template_data')) {
            if (isset($result['template_option_id']) && $result['template_option_id'] != 0) {
                $templateId = $this->optionResource->getTemplateIdFromTemplateOptionId($result['template_option_id']);
            }
            $data = [ 'option_id' => $result['option_id'], 'template_option_id' => $templateId];
            $data = $this->json->serialize($data);
            return $data;
        }
        if ($key =='option_visibility' && !$subject->hasData('option_visibility')) {
            $visibleData['visible_for_group_customer'] = $this->optionResource->getVisibleCustomer(
                $subject->getOptionId()
            );
            $visibleData['visible_for_store_view'] = $this->optionResource->getVisibleStore($subject->getOptionId());
            return $this->json->serialize($visibleData);
        }
        return $result;
    }
}
