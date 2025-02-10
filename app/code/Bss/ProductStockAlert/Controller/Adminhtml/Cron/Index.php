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
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Adminhtml\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\ProductStockAlert\Model\StockEmailProcessor
     */
    protected $stockEmailProcessor;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Bss\ProductStockAlert\Model\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBackend;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\ProductStockAlert\Model\StockEmailProcessor $stockEmailProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Bss\ProductStockAlert\Model\Form\FormKey $formKey
     * @param \Magento\Backend\Model\UrlInterface $urlHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\ProductStockAlert\Model\StockEmailProcessor $stockEmailProcessor,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Bss\ProductStockAlert\Model\Form\FormKey $formKey,
        \Magento\Backend\Model\UrlInterface $urlBackend
    ) {
        parent::__construct($context);
        $this->stockEmailProcessor = $stockEmailProcessor;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->urlBackend = $urlBackend;
    }

    /**
     * Execute cron now
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $baseUrl = $this->urlBackend->getBaseUrl();
        $refererUrl = $this->_redirect->getRefererUrl();
        if (strpos($baseUrl, $refererUrl) !== false) {
            $url = $refererUrl;
        } else {
            $url = $this->urlBackend->getUrl('adminhtml/system_config/edit', ['section' => 'bss_productstockalert']);
        }
        $resultRedirect->setUrl($url);

        $formKey = $this->getRequest()->getParam('form_key');
        if (!$formKey ||
            ($formKey != $this->formKey->getFormKey())) {
            $this->messageManager->addWarningMessage(__('Invalid form key.'));
            return $resultRedirect;
        }
        $collection = null;
        try {
            if ($this->getRequest()->getPostValue()) {
                $collection = $this->filter->getCollection($this->collectionFactory->create());
            }
            $this->stockEmailProcessor->process($collection);
        } catch (\Exception $e) {
            throw new \LogicException(__($e->getMessage()));
        }
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
