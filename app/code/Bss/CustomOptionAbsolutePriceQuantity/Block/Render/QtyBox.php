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
namespace Bss\CustomOptionAbsolutePriceQuantity\Block\Render;

use Magento\Framework\View\Element\Template;
use \Bss\CustomOptionAbsolutePriceQuantity\Helper\TierPriceOptionHelper;

class QtyBox extends \Magento\Framework\View\Element\Template
{
    /**
     * @var TierPriceOptionHelper
     */
    protected $tierPriceOptionHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * QtyBox constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param TierPriceOptionHelper $tierPriceOptionHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        TierPriceOptionHelper $tierPriceOptionHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->tierPriceOptionHelper = $tierPriceOptionHelper;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->setTemplate('Bss_CustomOptionAbsolutePriceQuantity::render/option-qty-box.phtml');
    }

    /**
     * @param mixed $amount
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->tierPriceOptionHelper->getFormatedPrice($amount);
    }

    /**
     * @param mixed $option
     * @return bool
     */
    public function checkSelectTypeOption($option)
    {
        if (in_array($option->getType(), TierPriceOptionHelper::SELECT_TYPE_OPTION)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTierPriceOption()
    {
        $option = $this->getOption();
        $productPrice = $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        /** @var \Magento\Catalog\Api\Data\ProductInterface $currentProduct */
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $productBasePrice = $this->getProduct()->getPrice();
        } else {
            $productBasePrice = $this->registry->registry('current_product')->getPrice();
        }

        $data = [];
        $this->tierPriceOptionHelper->addDataToTierPriceOption(
            $this->getProduct(),
            $data,
            $option,
            $productPrice,
            $productBasePrice
        );
        return$data;
    }

    /**
     * @param array $price
     * @param bool $excl
     * @return float
     */
    public function calculatorPrice($price, $excl = false)
    {
        return $this->tierPriceOptionHelper->calculatorPrice($price, $excl);
    }

    /**
     * @param float $price
     * @param float $optionPrice
     * @param string $priceType
     * @return float
     */
    public function calculatorSavePercent($price, $optionPrice, $priceType)
    {
        return $this->tierPriceOptionHelper->calculatorSavePercent($price, $optionPrice, $priceType);
    }

    /**
     * @return mixed
     */
    public function checkTypeProductPriceDisplay()
    {
        return $this->tierPriceOptionHelper->checkTypeProductPriceDisplay();
    }
}
