<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block;

class ArProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Construct function
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Function to verify if the current product is an AR Product
     *
     * @return bool
     */
    public function isAr()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product->getArType()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to get the AR file of the current product
     *
     * @return string|bool
     */
    public function getArProductData()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product->getArType()) {
            return $product->getArModelFileIos();
        } else {
            return false;
        }
    }
}
