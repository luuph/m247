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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Block\Catalog\Product\View;

use Magento\Framework\View\Element\Template;

class PriceAlert extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $magentoData;

    /**
     * Construct.
     *
     * @param Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Catalog\Helper\Data $magentoData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Catalog\Helper\Data $magentoData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
        $this->magentoData = $magentoData;
    }

    /**
     * Get from data.
     *
     * @return string
     */
    public function getFormDataActionUrl()
    {
        $url = $this->urlInterface->getUrl(
            'productstockalert/ajax/formDataPrice',
            ['product_id' => $this->getProduct()->getId()]
        );

        return $this->_escaper->escapeUrl($url);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product|boolean
     */
    public function getProduct()
    {
        $product = $this->magentoData->getProduct();
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }

    /**
     * Get full action name request.
     *
     * @return string
     */
    public function getActionController()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * Get product type
     *
     * @return array|string
     */
    public function getProductType()
    {
        return $this->getProduct()->getTypeId();
    }

    /**
     * Check display form price alert.
     *
     * @param string $template
     * @return PriceAlert
     */
    public function setTemplate($template)
    {
        if (!$this->helper->isEnablePriceAlertAndCustomer()
            || !$this->getProduct()
            || !$this->helper->checkProductType($this->getProduct()->getTypeId())
        ) {
            $template = ''; // Disable form price alert.
        }

        return parent::setTemplate($template);
    }
}
