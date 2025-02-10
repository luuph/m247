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
namespace Bss\ProductStockAlert\Model;

use Bss\ProductStockAlert\Model\ResourceModel\PriceAlert as PriceResource;
use Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class PriceAlertEmail
{
    /**
     * Limit email in all website
     *
     * @var array
     */
    protected $limitEmail = [];

    /**
     * Check config module enable all website
     *
     * @var array
     */
    protected $isEnable = [];

    /**
     * Email info all store view
     *
     * @var array
     */
    protected $emailInfo = [];

    /**
     * Currency rate in all currency (base_currency -> currency in key array)
     *
     * @var array
     */
    protected $currencyRate = [];

    /**
     * Currency symbol in all currency
     *
     * @var array
     */
    protected $currencySymbol = [];

    /**
     * Product data all store view. $productAllStore[product_id][store_id]
     *
     * @var array
     */
    protected $productAllStore = [];

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var PriceResource
     */
    protected $priceResource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Collection
     */
    protected $stockNotiCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Price alert Email Processor constructor.
     *
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param PriceResource $priceResource
     * @param StoreManagerInterface $storeManager
     * @param Collection $stockNotiCollection
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        PriceResource $priceResource,
        StoreManagerInterface $storeManager,
        Collection $stockNotiCollection,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->priceResource = $priceResource;
        $this->storeManager = $storeManager;
        $this->stockNotiCollection = $stockNotiCollection;
        $this->product = $product;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }

    /**
     * Execute send email
     *
     * @param array $collectionData
     * @return array
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function execute($collectionData = [])
    {
        $productIds = [];
        $priceAlertIdData = [];
        $result = [
            'total_success' => 0,
            'total_warning_limit' => 0,
            'total_warning_price' => 0,
            'total_error' => 0
        ];
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();

        if ($collectionData && isset($collectionData[0]['product_id'])) {
            $isMassCron = true;
        } else {
            $collectionData = $this->stockNotiCollection->addFieldToSelect('product_id')->groupByProductId()->getData();
            $isMassCron = false;
        }
        foreach ($collectionData as $datum) {
            $productIds[] = $datum['product_id'];
            if ($isMassCron) {
                $priceAlertIdData[] = $datum['id'];
            }
        }

        if ($isMassCron) {
            $productIds = array_unique($productIds);
        }

        foreach ($productIds as $productId) {
            if (!empty($priceAlertIdData)) {
                $data = $this->priceResource->getDataPrice($productId, $priceAlertIdData); // Run by mass action
            } else {
                $data = $this->priceResource->getDataPrice($productId); // Run by cron job
            }

            if ($data) {
                $this->getDataBeforeSendEmail($data, $productId, $baseCurrencyCode, $result);
            }
        }

        return $result;
    }

    /**
     * Get data price alert before send email
     *
     * @param array $data
     * @param string|int $productId
     * @param string $baseCurrencyCode
     * @param array $result
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function getDataBeforeSendEmail($data, $productId, $baseCurrencyCode, &$result)
    {
        $productData = $this->product->create()->load($productId);
        if (!$productData->getData()) {
            $this->logger->error(__('BSS Price Alert Email: Product ID %1 failed to load data.', $productId));
            $result['total_error']++;
            return;
        }

        foreach ($data as $item) {
            $isEnable = $this->isEnablePriceAlert((int)$item['website_id']);
            $limitEmail = $this->getLimitEmailPrice((int)$item['website_id']);

            if (!$isEnable) {
                continue;
            }

            if ((int)$item['send_count'] >= $limitEmail) { // Skip because email limit has been reached
                $result['total_warning_limit']++;
                continue;
            }

            // Convert price(default currency) to currency price alert.
            $rate = isset($item['currency_code']) ? $this->getRate($baseCurrencyCode, $item['currency_code']) : 0;
            if ((float)$rate <= 0) { // Skip because currency code incorrect
                $this->logger->error(__('BSS Price Alert Email: Price Alert ID %1 failed to load data. Please check the "bss_product_alert_price" table and delete it if needed.', $item["id"]));
                $result['total_error']++;
                continue;
            }

            if (!empty($item['final_price'])) { // All product has final price.
                $finalPrice = number_format((float)$item['final_price'] / $rate, 2);
            } else { // Bundle product dynamic price.
                $finalPrice = number_format((float)$item['min_price'] / $rate, 2);
            }

            if ((float)$finalPrice === (float)$item['initial_price']) { // Skip because price not change
                $result['total_warning_price']++;
                continue;
            }

            $storeId = (int)$item['store_id'];

            if (!isset($this->emailInfo[$storeId]['sender']) || !isset($this->emailInfo[$storeId]['email'])) {
                $this->emailInfo[$storeId]['sender'] = $this->helper->getEmailPriceInfo($storeId);
                $this->emailInfo[$storeId]['template'] = $this->helper->getEmailPriceTemplate($storeId);
                $this->emailInfo[$storeId]['email'] = $this->helper->getEmailPriceEmail($this->emailInfo[$storeId]['sender'], $storeId);
            }

            $itemData['price_data'] = $item;
            $itemData['price_data']['final_price'] = $finalPrice;
            $itemData['price_data']['currency_symbol'] = $this->getCurrencySymbol($item['currency_code']);
            $itemData['price_data']['website_name'] = $this->storeManager->getWebsite((int)$item['website_id'])->getName();
            $itemData['price_data']['store_url'] = $this->storeManager->getStore($storeId)->getBaseUrl();
            $itemData['price_data']['sender_name'] = $this->emailInfo[$storeId]['sender'];
            $itemData['price_data']['sender_email'] = $this->emailInfo[$storeId]['email'];

            if (empty($this->productAllStore[$productId][$storeId])) {
                $this->productAllStore[$productId][$storeId] = $productData->setStoreId($storeId);
            }
            $product = $this->productAllStore[$productId][$storeId];
            $itemData['product_data'] = $product->getData();
            $itemData['product_data']['img_price_alert'] = $product->getMediaGalleryImages()->getFirstItem()->getUrl();
            $itemData['product_data']['url_price_alert'] = $product->getUrlInStore();
            $itemData['product_data']['parent_id'] = $item['parent_id'];

            // Send email
            $this->sendEmail($itemData, $result);
        }
    }

    /**
     * Execute send email
     *
     * @param array $item
     * @param array $result
     * @return void
     */
    public function sendEmail($item, &$result)
    {
        try {
            $storeId = (int)$item['price_data']['store_id'];
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $templateVars = [
                'customerName' => $item['price_data']['customer_name'],
                'productData' => $item['product_data'],
                'priceData' => $item['price_data'],
            ];
            if (!isset($this->emailInfo[$storeId])) {
                $this->emailInfo[$storeId]['sender'] = $this->helper->getEmailPriceInfo($storeId);
                $this->emailInfo[$storeId]['template'] = $this->helper->getEmailPriceTemplate($storeId);
            }
            $emailSender = $this->emailInfo[$storeId]['sender'];
            $emailTemplate = $this->emailInfo[$storeId]['template'];

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($emailSender)
                ->addTo($item['price_data']['customer_email'], $item['price_data']['customer_name'])
                ->getTransport();

            $transport->sendMessage();

            $this->updatePriceAlert(
                (int)$item['price_data']['id'],
                (float)$item['price_data']['final_price'],
                (int)$item['price_data']['send_count'],
                (int)$item['price_data']['website_id']
            );
            $result['total_success']++;

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->error(__("BSS Price Alert Email: %1", $e->getMessage()));
            $result['total_error']++;
        }
    }

    /**
     * Update send count, send status and price subscribe
     *
     * @param int $priceAlertId
     * @param float $newPrice
     * @param int $sendCount
     * @param int $websiteId
     * @return void
     */
    public function updatePriceAlert($priceAlertId, $newPrice, $sendCount, $websiteId)
    {
        $data = [
            'send_count' => ++$sendCount,
            'initial_price' => $newPrice, // Update initial_price for the next emails.
            'status' => \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_SENT
        ];

        if ($sendCount >= $this->getLimitEmailPrice($websiteId)) {
            $data['status'] = \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_SENTLIMIT;
        }

        $this->priceResource->updatePriceAlert(
            $priceAlertId,
            $data
        );
    }

    /**
     * Check module enable with website id.
     *
     * @param int $websiteId
     * @return int|mixed
     */
    public function isEnablePriceAlert($websiteId)
    {
        if (empty($this->isEnable[$websiteId])) {
            $this->isEnable[$websiteId] = $this->helper->isEnablePriceAlert($websiteId);
        }

        return $this->isEnable[$websiteId];
    }

    /**
     * Get limit email price with website id.
     *
     * @param int $websiteId
     * @return int|mixed
     */
    public function getLimitEmailPrice($websiteId)
    {
        if (empty($this->limitEmail[$websiteId])) {
            $this->limitEmail[$websiteId] = $this->helper->getLimitEmailPrice($websiteId);
        }

        return $this->limitEmail[$websiteId];
    }

    /**
     * Get rate
     *
     * @param string $baseCurrencyCode
     * @param string $toCurrency
     * @return float|mixed
     */
    public function getRate($baseCurrencyCode, $toCurrency)
    {
        if ($toCurrency === $baseCurrencyCode) {
            return 1;
        }

        if (empty($this->currencyRate[$toCurrency])) {
            $currencyModel = $this->currencyFactory->create()->load($toCurrency);

            $this->currencySymbol[$toCurrency] = $currencyModel->getCurrencySymbol(); // Get currency symbol with currency code optimize performance
            $this->currencyRate[$toCurrency] = $currencyModel->getRate($baseCurrencyCode);
        }

        return $this->currencyRate[$toCurrency];
    }

    /**
     * Get currency symbol with currency code
     *
     * @param string $currencyCode
     * @return mixed|string
     */
    public function getCurrencySymbol($currencyCode)
    {
        try {
            if (empty($this->currencySymbol[$currencyCode])) {
                $currencyModel = $this->currencyFactory->create()->load($currencyCode);
                $this->currencySymbol[$currencyCode] = $currencyModel->getCurrencySymbol();
            }

            return $this->currencySymbol[$currencyCode];
        } catch (\Exception $e) {
            $this->logger->error(__("BSS Price Alert Email: %1", $e->getMessage()));
            return $currencyCode;
        }
    }
}
