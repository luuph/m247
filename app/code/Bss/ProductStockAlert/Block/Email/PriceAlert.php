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
namespace Bss\ProductStockAlert\Block\Email;

use Bss\ProductStockAlert\Helper\Data;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class PriceAlert extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'email/price_alert.phtml';

    /**
     * @var string|null;
     */
    protected $baseUrl;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * Current Store scope object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct.
     *
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param Data $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper,
        \Bss\ProductStockAlert\Helper\Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Magento escape methods
     *
     * @return \Magento\Framework\Escaper
     */
    public function escaper()
    {
        return $this->escaper;
    }

    /**
     * Get base url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        if (empty($this->baseUrl)) {
            $this->baseUrl = $this->storeManager->getStore()->getBaseUrl();
        }
        return $this->baseUrl;
    }

    /**
     * Get product data
     *
     * @return array|mixed|null
     */
    public function getProduct()
    {
        return $this->getData('product_data');
    }

    /**
     * Get price data
     *
     * @return array|mixed|null
     */
    public function getPriceData()
    {
        return $this->getData('price_data');
    }

    /**
     * Get url list price alert
     *
     * @return array|string
     * @throws NoSuchEntityException
     */
    public function getUrlListPriceAlert()
    {
        return $this->escaper->escapeUrl($this->getBaseUrl() . "productstockalert/pricealert");
    }

    /**
     * Get action post in form add to cart
     *
     * @return array|string
     * @throws NoSuchEntityException
     */
    public function getAddToCartAction()
    {
        return $this->escaper->escapeUrl($this->getBaseUrl() . "productstockalert/cart/addtocart");
    }

    /**
     * Get cancel price alert url
     *
     * @param string $productId
     * @param string $parentId
     * @return array|string
     * @throws NoSuchEntityException
     */
    public function getCancelPostAction($productId, $parentId)
    {
        return $this->escaper->escapeUrl(
            $this->getBaseUrl() . "productstockalert/unsubscribe/pricealert?product_id=" . $productId . "&parent_id=" . $parentId
        );
    }

    /**
     * Get sender name
     *
     * @param string|int $storeId
     * @return array|string
     */
    public function getEmailPriceName($storeId)
    {
        return $this->escaper->escapeHtml($this->helper->getEmailPriceName($storeId));
    }
}
