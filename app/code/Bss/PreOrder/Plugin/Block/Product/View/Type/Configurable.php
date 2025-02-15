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
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Block\Product\View\Type;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Model\PreOrderAttribute;

class Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    private $linkData;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Bss\PreOrder\Helper\ProductData $linkData
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Bss\PreOrder\Helper\ProductData $linkData,
        Data $helper
    ) {
        $this->linkData = $linkData;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->helper = $helper;
    }

    /**
     * Apply json Child Pre Order
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $childProduct = $this->linkData->getAllData($subject->getAllowProducts());
        $config = $this->jsonDecoder->decode($result);
        $config["preorder"] = $childProduct;
        $config["preorder_allow_mixin"] = $this->helper->isMix();
        $config["isEnabledPackage"] = $this->helper->checkProductConfigurableGridView();
        return $this->jsonEncoder->encode($config);
    }
}
