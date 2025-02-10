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
namespace Bss\ProductStockAlert\Block\Customer;

class ListPriceAlert extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\CollectionFactory
     */
    protected $modelCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var array|null
     */
    protected $productData;

    /**
     * List Price Alert Constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\CollectionFactory $modelCollectionFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $product,
        \Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\CollectionFactory $modelCollectionFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->product = $product;
        $this->helper = $helper;
        $this->modelCollectionFactory = $modelCollectionFactory;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Preparing global layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Price Alert Subscription'));
        if ($this->getItems()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'magecomp.category.pager'
            )->setAvailableLimit([5=>5,10=>10,15=>15])->setShowPerPage(true)->setCollection(
                $this->getItems()
            );
            $this->setChild('pager', $pager);
            $this->getItems()->load();
        }
    }

    /**
     * Get items data in page.
     *
     * @return \Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\Collection|\Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        //get values of current page
        $page = $this->getRequest()->getParam('p') ?: 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ?: 5;

        $newsCollection = $this->modelCollectionFactory->create();
        $newsCollection->addFieldToFilter('customer_id', ['eq' => $this->helper->getCustomerId()]);
        $newsCollection->addFieldToFilter('website_id', ['eq' => $this->helper->getWebsiteId()]);
        $newsCollection->setPageSize($pageSize);
        $newsCollection->setCurPage($page);

        return $newsCollection;
    }

    /**
     * Get product by product id.
     *
     * @param string $productId
     * @return \Magento\Catalog\Model\Product|mixed
     */
    public function getProduct($productId)
    {
        if (empty($this->productData[$productId])) {
            $this->productData[$productId] = $this->product->create()->load($productId);
        }

        return $this->productData[$productId];
    }

    /**
     * Get product url.
     *
     * @param string $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        $product = $this->getProduct($productId);
        return $product->getProductUrl();
    }

    /**
     * Get product name.
     *
     * @param string $productId
     * @return string
     */
    public function getProductName($productId)
    {
        $product = $this->getProduct($productId);
        return $product->getName();
    }

    /**
     * Get product image url
     *
     * @param string $productId
     * @return string|mixed
     */
    public function getProductImageUrl($productId)
    {
        $product = $this->getProduct($productId);
        return $product->getMediaGalleryImages()->getFirstItem()->getUrl();
    }

    /**
     * Get final price.
     *
     * @param string $productId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductFinalPrice($productId)
    {
        $product = $this->getProduct($productId);
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();

        if (empty($currency)) {
            $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
            $currency = $this->currencyFactory->create()->load($currencyCode);
        }

        return $this->priceCurrency->format($price, false, 2, null, $currency);
    }

    /**
     * Get url cancel price alert.
     *
     * @param string $productId
     * @return string
     */
    public function getUnsubUrl($productId)
    {
        return $this->getUrl(
            'productstockalert/unsubscribe/PriceAlert',
            [
                'product_id' => $productId,
                'backurl' => '1'
            ]
        );
    }

    /**
     * Get url cancel all price alert.
     *
     * @return string
     */
    public function getUnsubAllUrl()
    {
        return $this->getUrl(
            'productstockalert/unsubscribe/PriceAlertAll'
        );
    }

    /**
     * Get pager HTML.
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get stop notify text.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getStopNotifyText()
    {
        return $this->helper->getStopButtonText();
    }

    /**
     * Get stop all notify text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getStopAllNotifyText()
    {
        return __('Stop All Notification');
    }

    /**
     * Get btn background color.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonColor()
    {
        return $this->helper->getButtonColor();
    }

    /**
     * Get btn text color.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonTextColor()
    {
        return $this->helper->getButtonTextColor();
    }

    /**
     * Escaper func
     *
     * @return \Magento\Framework\Escaper
     */
    public function escaper()
    {
        return $this->_escaper;
    }
}
