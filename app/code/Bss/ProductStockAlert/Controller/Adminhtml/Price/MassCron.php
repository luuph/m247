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
 * @copyright  Copyright (c) 2021-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Adminhtml\Price;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Bss\ProductStockAlert\Model\ResourceModel\PriceAlert\CollectionFactory;
use Bss\ProductStockAlert\Model\Stock as Stock;

class MassCron extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Stock
     */
    protected $model;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Bss\ProductStockAlert\Model\PriceAlertEmail
     */
    protected $stockEmailProcessor;

    /**
     * Mass cron constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param \Bss\ProductStockAlert\Model\PriceAlertEmail $stockEmailProcessor
     * @param CollectionFactory $collectionFactory
     * @param Stock $model
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Bss\ProductStockAlert\Model\PriceAlertEmail $stockEmailProcessor,
        CollectionFactory $collectionFactory,
        Stock $model
    ) {
        $this->filter = $filter;
        $this->stockEmailProcessor = $stockEmailProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->model = $model;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }


    /**
     * Execute send mail by mass action.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|(\Magento\Framework\Controller\Result\Redirect&\Magento\Framework\Controller\ResultInterface)|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionData = $collection->getData();
            $totalRecords = $collection->getSize();

            $result = $this->stockEmailProcessor->execute($collectionData);
        } catch (\Exception $e) {
            throw new \LogicException(__($e->getMessage()));
        }

        if (isset($result['total_success'])) { // Still show when total_success = 0
            $this->messageManager->addSuccessMessage(__('Email notification of price change has been successfully sent to %1/%2 records!', (int)$result['total_success'], $totalRecords));
        }

        if (!empty($result['total_warning_price'])) {
            $this->messageManager->addNoticeMessage(__('%1 records have not been sent. Please check if the prices are updated.', (int)$result['total_warning_price']));
        }

        if (!empty($result['total_warning_limit'])) {
            $this->messageManager->addWarningMessage(__('%1 records have not been sent. Please check if Limit Email Send per Customer is reached.', (int)$result['total_warning_limit']));
        }

        if (!empty($result['total_error'])) {
            $this->messageManager->addErrorMessage(__('%1 records have not been sent. Please check the file var/log/debug.log for error details.', (int)$result['total_error']));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductStockAlert::manage');
    }
}
