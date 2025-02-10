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
namespace Bss\RewardPoint\Plugin\Block\Product\View\Type;

class Configurable
{
    /**
     * @var \Bss\RewardPoint\Helper\ProductData
     */
    private $linkData;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * Configurable constructor.
     * @param \Bss\RewardPoint\Helper\ProductData $linkData
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Bss\RewardPoint\Helper\ProductData $linkData,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->linkData = $linkData;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Plugin after jsonConfig product data
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $childProduct = $this->linkData->getAllData($subject->getProduct()->getEntityId());
        $config = $this->jsonSerializer->unserialize($result);
        $config["childProduct"] = $childProduct;
        return $this->jsonSerializer->serialize($config);
    }
}
