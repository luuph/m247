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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin\Model\ResourceModel;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOptionValue;
use Bss\CustomOptionAbsolutePriceQuantity\Model\TierPriceOptionValueFactory;
use Magento\Framework\Exception\LocalizedException;

class OptionValuePlugin
{
    /**
     * @var TierPriceOptionValueFactory
     */
    protected $tierPriceOptionValueFactory;

    /**
     * @var TierPriceOptionValue
     */
    protected $resourceTierPriceOptionValue;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;
    /**
     * OptionValuePlugin constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param TierPriceOptionValueFactory $tierPriceOptionValueFactory
     * @param TierPriceOptionValue $resourceTierPriceOptionValue
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        TierPriceOptionValueFactory $tierPriceOptionValueFactory,
        TierPriceOptionValue $resourceTierPriceOptionValue,
        ModuleConfig $moduleConfig
    ) {
        $this->tierPriceOptionValueFactory =$tierPriceOptionValueFactory;
        $this->resourceTierPriceOptionValue =$resourceTierPriceOptionValue;
        $this->request = $request;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * After Plugin Save Value
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $subject
     * @param \Magento\Catalog\Model\Product\Option\Value $result
     * @return \Magento\Catalog\Model\Product\Option\Value
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterSave(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $result
    ) {
        $tierPriceModel = $this->tierPriceOptionValueFactory->create()->loadByOptionTyeId($subject->getOptionTypeId());
        if (!$tierPriceModel) {
            $tierPriceModel = $this->tierPriceOptionValueFactory->create();
        }
        $tierPrice = '';

        if ($subject->getData('bss_tier_price_option') != '0') {
            $tierPrice = $subject->getData('bss_tier_price_option');
        }
        if ($this->moduleConfig->isModuleEnable() && $tierPrice) {
            $tierPriceModel->setOptionTypeId($subject->getOptionTypeId())
                ->setTierPrice($tierPrice);
            $this->resourceTierPriceOptionValue->save($tierPriceModel);
        }
        return $result;
    }

    /**
     * After get data
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $subject
     * @param mixed $result
     * @param string $key
     * @param int $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetData(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $result,
        $key = '',
        $index = null
    ) {
        $tierPriceModel = $this->tierPriceOptionValueFactory->create();
        if ($key === '') {
            if (isset($result['option_type_id']) && !isset($result['bss_tier_price_option'])) {
                $tierPrice = $tierPriceModel->getTierPrice($result['option_type_id'], 'tier_price');
                $result['bss_tier_price_option'] = $tierPrice;
            }
        }
        if ($key === 'bss_tier_price_option'
            && $subject->getData('option_type_id')
            && !$subject->hasData('bss_tier_price_option')
            && isset($result['option_type_id'])
        ) {
            $tierPrice = $tierPriceModel->getTierPrice($result['option_type_id'], 'tier_price');
            return $tierPrice;
        }
        return $result;
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
            ['optabs' => $subject->getCollection()->getTable('bss_tier_price_product_option_type_value')],
            'optabs.option_type_id=main_table.option_type_id',
            'tier_price'
        );
        foreach ($result as $value) {
            if ($value->getData('tier_price')) {
                $value->setBssTierPriceOption($value->getData('tier_price'));
            }
        }
        return $result;
    }
}
