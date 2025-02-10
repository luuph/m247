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
namespace Bss\CustomOptionTemplate\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Exception\NoSuchEntityException;

class OptionValuePlugin
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
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Option\Value
     */
    protected $valueResource;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Option
     */
    protected $optionResource;

    /**
     * OptionValuePlugin constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\Option\Value $valueResource
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\Option $optionResource
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\CustomOptionTemplate\Model\ResourceModel\Option\Value $valueResource,
        \Bss\CustomOptionTemplate\Model\ResourceModel\Option $optionResource
    ) {
        $this->json = $json;
        $this->productRepository = $productRepository;
        $this->valueResource = $valueResource;
        $this->optionResource = $optionResource;
    }

    /**
     * Set exclude template
     * @param Value $subject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSave(
        \Magento\Catalog\Model\Product\Option\Value $subject
    ) {
        try {
            $product = $this->productRepository->getById($subject->getProductId());
        } catch (NoSuchEntityException $ex) {
            $product = null;
        }
        if ($product && $product->getData('tenplates_excluded')) {
            if ($subject->getOption()->getData('template_option_id') == 0) {
                $subject->setData('template_option_type_id', 0);
            }
        }
    }

    /**
     * Set is default value
     * @param Value $subject
     * @param Value $result
     * @return Value
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $result
    ) {
        if ($subject->getId()) {
            // add visible option
            $this->valueResource->addIsDefaultForValue(
                $subject->getId(),
                ['is_default' => $subject->getData('is_default')]
            );
        }
        return $result;
    }

    /**
     * AroundGetData
     *
     * @param Value $subject
     * @param mixed $result
     * @param string $key
     * @param string $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        Value $subject,
        $result,
        $key = '',
        $index = null
    ) {
        $isDefault = 0;
        if ($key === '') {
            if (isset($result['template_option_type_id'])) {
                $isDefault = $this->valueResource->checkIsDefault($subject->getId());
                $isDefault = $isDefault ? $isDefault : 0;
            }
            $result['is_default'] = $isDefault;
        }
        if ($key === 'is_default' && !$subject->hasData('is_default')) {
            $isDefault = $this->valueResource->checkIsDefault($subject->getId());
            $isDefault = $isDefault ? $isDefault : 0;
            return $isDefault;
        }

        if ($this->checkExistParamTitleOption($key, $subject)) {
            $titleOption = $this->valueResource->getTitleValue($subject->getData('template_option_type_id'));
            return $titleOption;
        }
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $subject
     * @return bool
     */
    private function checkExistParamTitleOption($key, $subject)
    {
        return $key === 'title_option'
            && !$subject->hasData('title_option')
            && $subject->getData('template_option_type_id');
    }

    /**
     * Add data to value
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection $result
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterGetValuesCollection(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $result
    ) {
        $result->getSelect()->joinLeft(
            ['optype_isdefault' => $subject->getCollection()->getTable('bss_custom_option_value_default')],
            'optype_isdefault.option_type_id=main_table.option_type_id',
            [
                'is_default' => 'optype_isdefault.is_default'
            ]
        );
        foreach ($result as $value) {
            if ($value->getIsDefault() == 1) {
                $value->setIsDefault($value->getIsDefault());
            }
        }
        return $result;
    }
}
