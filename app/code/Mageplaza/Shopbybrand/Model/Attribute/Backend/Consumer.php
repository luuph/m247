<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\Shopbybrand\Model\Attribute\Backend;

use Exception;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Bulk\OperationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\Service\BrandUsagePublisher;
use Psr\Log\LoggerInterface;

/**
 * Class Consumer
 * @package Mageplaza\Shopbybrand\Model\Attribute\Backend
 */
class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Processor
     */
    private $productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $productPriceIndexerProcessor;

    /**
     * @var Product
     */
    private $catalogProduct;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OperationManagementInterface
     */
    private $operationManagement;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * @param Product $catalogProduct
     * @param Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param OperationManagementInterface $operationManagement
     * @param Action $action
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     * @param BrandHelper $helper
     */
    public function __construct(
        Product $catalogProduct,
        Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        OperationManagementInterface $operationManagement,
        Action $action,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManager $entityManager,
        BrandHelper $helper
    ) {
        $this->catalogProduct               = $catalogProduct;
        $this->productFlatIndexerProcessor  = $productFlatIndexerProcessor;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productAction                = $action;
        $this->logger                       = $logger;
        $this->serializer                   = $serializer;
        $this->operationManagement          = $operationManagement;
        $this->entityManager                = $entityManager;
        $this->brandHelper                  = $helper;
    }

    /**
     * Process
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     *
     * @return void
     * @throws Exception
     *
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data           = $this->serializer->unserialize($serializedData);
            $this->execute($data);
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof \Magento\Framework\DB\Adapter\LockWaitException
                || $e instanceof \Magento\Framework\DB\Adapter\DeadlockException
                || $e instanceof \Magento\Framework\DB\Adapter\ConnectionException
            ) {
                $status    = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message   = $e->getMessage();
            } else {
                $status    = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message   = __(
                    'Sorry, something went wrong during product attributes update. Please see log for details.'
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            $status    = ($e instanceof TemporaryStateExceptionInterface)
                ? OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
                : OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message   = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $status    = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message   = $e->getMessage();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $status    = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message   = __(
                'Sorry, something went wrong during product attributes update. Please see log for details.'
            );
            $message   .= $e->getMessage();
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }

    /**
     * Execute
     *
     * @param array $data
     *
     * @return void
     * @throws NoSuchEntityException
     */
    private function execute($data): void
    {
        $productIdsArray = json_decode($data['product_ids'], true);
        if ($data['action'] === BrandUsagePublisher::ADD_BRAND) {
            foreach ($productIdsArray as $productId) {
                $this->brandHelper->setBrand($productId, $data['option_id'], $data['store_id']);
            }
        } elseif ($data['action'] === BrandUsagePublisher::REMOVE_BRAND) {
            foreach ($productIdsArray as $productId) {
                $this->brandHelper->unSetBrand($productId, $data['store_id']);
            }
        }
        $this->brandHelper->indexProducts($productIdsArray);
        $qty = $this->brandHelper->collectProductQty(
            $data['option_id'],
            $this->brandHelper->getAttributeCode($data['store_id'])
        );
        $this->brandHelper->saveQtyOption(
            $data['option_id'],
            $this->brandHelper->getAttributeCode($data['store_id']),
            $qty,
            $data['store_id']
        );
    }
}
