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

use Magento\Framework\App\Action\HttpGetActionInterface;

class SessionProductAlert extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelStock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Construct.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helperData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\ProductStockAlert\Model\Stock $modelStock
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helperData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductStockAlert\Model\Stock $modelStock,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->configurable = $configurable;
        $this->storeManager = $storeManager;
        $this->modelStock = $modelStock;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Get all product stock alert.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = [];
        $data['btn_text_stop'] = $this->helperData->getStopButtonText();

        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $data['product'] = [];
            $items = $this->modelStock->getStockNotice(
                ['customer_id' => $customerId, 'website_id' => $currentWebsiteId],
                ['customer_email', 'product_id', 'parent_id']
            );
            foreach ($items as $item) {
                $data['product'][$item['product_id']]['email'] = $item['customer_email'];
                $data['product'][$item['product_id']]['parent_id'] = $item['parent_id'];
            }
        } else {
            $data['product'] = $this->customerSession->getNotifySubscription() ?: [];
        }

        foreach ($data['product'] as $productId => $value) {
            if (isset($value['website']) && $value['website'] != $currentWebsiteId) {
                unset($data['product'][$productId]);
                continue;
            }

            if ($customerId) {
                $parentId = $value['parent_id'];
            } else {
                $parentId = $this->configurable->getParentIdsByChild($productId)[0] ?? null;
            }

            $data['product'][$productId]['url_cancel'] = $this->helperData->getCancelPostAction($productId, $parentId);
        }

        return $this->resultJsonFactory->create()
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
            ->setData($data);
    }
}
