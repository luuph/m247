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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin\Model\ResourceModel;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\TierPriceOptionHelper;

class OptionPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption
     */
    protected $resourceTierPriceOption;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\TierPriceOptionFactory
     */
    protected $tierPriceOptionFactory;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOption
     */
    protected $descriptionOption;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOptionType
     */
    protected $descriptionOptionType;

    /**
     * OptionPlugin constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\TierPriceOptionFactory $tierPriceOptionFactory
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption $resourceTierPriceOption
     * @param ModuleConfig $moduleConfig
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOption $descriptionOption
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOptionType $descriptionOptionType
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\TierPriceOptionFactory $tierPriceOptionFactory,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption $resourceTierPriceOption,
        ModuleConfig $moduleConfig,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOption $descriptionOption,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\DescriptionOptionType $descriptionOptionType
    ) {
        $this->tierPriceOptionFactory =$tierPriceOptionFactory;
        $this->resourceTierPriceOption =$resourceTierPriceOption;
        $this->request = $request;
        $this->moduleConfig = $moduleConfig;
        $this->descriptionOption = $descriptionOption;
        $this->descriptionOptionType = $descriptionOptionType;
    }

    /**
     * After Plugin Save Value
     *
     * @param \Magento\Catalog\Model\Product\Option $subject
     * @param \Magento\Catalog\Model\Product\Option $result
     * @return \Magento\Catalog\Model\Product\Option
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(
        \Magento\Catalog\Model\Product\Option $subject,
        $result
    ) {
        if ($this->moduleConfig->isModuleEnable()
            && !in_array($subject->getType(), TierPriceOptionHelper::SELECT_TYPE_OPTION)) {
            $tierPriceModel = $this->tierPriceOptionFactory->create()->loadByOptionId($subject->getOptionId());
            if (!$tierPriceModel) {
                $tierPriceModel = $this->tierPriceOptionFactory->create();
            }
            $tierPrice = '';
            if ($subject->getData('bss_tier_price_option') != '0') {
                $tierPrice = $subject->getData('bss_tier_price_option');
            }
            if ($tierPrice) {
                $tierPriceModel->setOptionId($subject->getOptionId())
                    ->setTierPrice($tierPrice);
                $this->resourceTierPriceOption->save($tierPriceModel);
            }
        }

        if ($this->moduleConfig->isModuleEnable()) {
            $this->descriptionOptionType->saveDescriptionType($result);
            $this->descriptionOption->saveDescription($result);
        }

        return $result;
    }

    /**
     * After get data
     *
     * @param \Magento\Catalog\Model\Product\Option $subject
     * @param mixed $result
     * @param string $key
     * @param int $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetData(
        \Magento\Catalog\Model\Product\Option $subject,
        $result,
        $key = '',
        $index = null
    ) {
        $tierPriceModel = $this->tierPriceOptionFactory->create();
        if (!in_array($subject->getType(), TierPriceOptionHelper::SELECT_TYPE_OPTION)) {
            if ($key === '') {
                if (isset($result['option_id']) && !isset($result['bss_tier_price_option'])) {
                    $tierPrice = $tierPriceModel->getTierPrice($result['option_id'], 'tier_price');
                    $result['bss_tier_price_option'] = $tierPrice;
                }
            }
        }
        if ($key === 'bss_tier_price_option'
            && $subject->getData('option_id')
            && $result == null
        ) {
            $tierPrice = $tierPriceModel->getTierPrice($subject->getData('option_id'), 'tier_price');
            return $tierPrice;
        }
        return $result;
    }
}
