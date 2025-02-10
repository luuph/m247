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
namespace Bss\ProductStockAlert\Controller\Ajax;

use Bss\ProductStockAlert\Helper\Data;
use Bss\ProductStockAlert\Model\PriceAlertHelper;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class FormDataPrice extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    const JSON_DATA_CONFIG_DISPATCHER = 'json_data_config_dispatcher';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceAlertHelper
     */
    protected $priceAlertHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepository $productRepository
     * @param Data $helperData
     * @param DataObjectFactory $dataObjectFactory
     * @param FormKey $formKey
     * @param StoreManagerInterface $storeManager
     * @param PriceAlertHelper $priceAlertHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context            $context,
        JsonFactory                                    $resultJsonFactory,
        ProductRepository                              $productRepository,
        Data                                           $helperData,
        DataObjectFactory                              $dataObjectFactory,
        FormKey                                        $formKey,
        StoreManagerInterface                          $storeManager,
        PriceAlertHelper                               $priceAlertHelper,
        \Magento\Customer\Model\Session                $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->priceAlertHelper = $priceAlertHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * Execute.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $productId = $this->getRequest()->getParam('product_id');
            if (!$productId) {
                return $this->buildErrorResponse();
            }

            $product = $this->productRepository->getById($productId);

            if (!$product || !$product->getId()) {
                return $this->buildErrorResponse();
            }

            $data = $this->getFormData($product);
            return $this->resultJsonFactory->create()
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
                ->setData($data);
        } catch (\Exception $e) {
            return $this->buildErrorResponse();
        }
    }

    /**
     * Get form data.
     *
     * @param Product $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFormData($product)
    {
        $formKey = $this->formKey->getFormKey();
        $productType = $product->getTypeId();

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $data = $this->createConfigurableRender($product, $formKey);
        } else { // always create parent product
            $data = $this->createParentProductRender($product, $formKey, $productType);
        }

        $formDataObject = $this->dataObjectFactory->create()->setData($data);
        $this->_eventManager->dispatch(self::JSON_DATA_CONFIG_DISPATCHER, ['data' => $formDataObject]);
        return $formDataObject->getData();
    }

    /**
     * Create render data configurable product.
     *
     * @param Product $product
     * @param string $formKey
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws LocalizedException
     */
    private function createConfigurableRender($product, $formKey)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getUsedProductCollection($product);
        $skeleton = $this->createSkeletonResponse(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $merger = [];
        $data['url'] = $product->getUrlInStore();
        $data['currency_code'] = $product->getStore()->getCurrentCurrency()->getCode();
        $data['website_id'] = $this->storeManager->getStore()->getWebsiteId();

        foreach ($childItems as $childItem) {
            $pid = $childItem['entity_id'];
            $parId = $product->getId();
            $data['initial_price'] = $childItem->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
            $data['sku'] = $childItem->getSku();

            $merger[$pid] = $this->createSkeletonAdditional($pid, $parId, $formKey, $data);
        }

        return $this->addAdditionalDataResponse($skeleton, $merger);
    }

    /**
     * Create render data product.
     *
     * @param Product $product
     * @param string $formKey
     * @param string $productType
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws LocalizedException
     */
    private function createParentProductRender($product, $formKey, $productType = 'simple')
    {
        $skeleton = $this->createSkeletonResponse($productType);
        $pid = $product->getId();
        $data['url'] = $product->getUrlInStore();
        $data['initial_price'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $data['currency_code'] = $product->getStore()->getCurrentCurrency()->getCode();
        $data['sku'] = $product->getSku();
        $data['website_id'] = $this->storeManager->getStore()->getWebsiteId();

        $additionalData =  [
            $pid => $this->createSkeletonAdditional($pid, $pid, $formKey, $data)
        ];
        return $this->addAdditionalDataResponse($skeleton, $additionalData);
    }

    /**
     * Build error response
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function buildErrorResponse()
    {
        return $this->resultJsonFactory->create()->setData([
            '_reload' => 1,
            '_error' => 1
        ]);
    }

    /**
     * Add data additional
     *
     * @param array $skeletonResponse
     * @param array $additionalData
     * @return array
     */
    private function addAdditionalDataResponse($skeletonResponse, $additionalData)
    {
        $skeletonResponse['product_data'] = $additionalData;
        return $skeletonResponse;
    }

    /**
     * Data product price alert
     *
     * @param int $pid
     * @param int $parId
     * @param string $formKey
     * @param array $data
     * @return array
     */
    private function createSkeletonAdditional($pid, $parId, $formKey, $data)
    {
        return [
            'has_email' => $this->helperData->hasEmailPrice($pid),
            'initial_price' => $data['initial_price'],
            'currency_code' => $data['currency_code'],
            'product_sku' => $data['sku'],
            'url_product' => $data['url'],
            'form_action' => $this->priceAlertHelper->getAddPostAction($pid, $parId),
            'form_key' => $formKey,
            'product_id' => $pid,
            'parent_id' => $parId,
            'form_action_cancel' => $this->priceAlertHelper->getCancelPostAction($pid, $parId)
        ];
    }

    /**
     * Data config
     *
     * @param string $type
     * @return array
     */
    private function createSkeletonResponse($type)
    {
        $defaultEmail = $this->customerSession->getCustomer()->getData('email') ?? '';

        return [
            'title' => $this->helperData->getNotifyPriceMessage(),
            'label' => '',
            'button_text' => $this->helperData->getButtonText(),
            'stop_button_text' => $this->helperData->getStopButtonText(),
            'button_style' => $this->helperData->getButtonColor(),
            'button_text_color' => $this->helperData->getButtonTextColor(),
            'title_cancel' => $this->helperData->getStopNotifyPriceMessage(),
            'button_text_cancel' => $this->helperData->getStopButtonText(),
            'has_options' => 1,
            'type' => $type,
            'default_email' => $defaultEmail
        ];
    }
}
