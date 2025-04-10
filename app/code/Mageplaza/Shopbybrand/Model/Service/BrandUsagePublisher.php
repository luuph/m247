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

namespace Mageplaza\Shopbybrand\Model\Service;

use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Authorization\Model\UserContextInterface;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class BrandUsagePublisher
 * @package Mageplaza\Shopbybrand\Model\Service
 */
class BrandUsagePublisher
{
    private const TOPIC_NAME = 'mpbrand.update.attribute';

    const REMOVE_BRAND = 'remove_brand';
    const ADD_BRAND    = 'add_brand';

    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var int
     */
    private $bulkSize;

    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operartionFactory
     * @param IdentityGeneratorInterface $identityService
     * @param SerializerInterface $serializer
     * @param UserContextInterface $userContext
     * @param BrandHelper $helper
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operartionFactory,
        IdentityGeneratorInterface $identityService,
        SerializerInterface $serializer,
        UserContextInterface $userContext,
        BrandHelper $helper
    ) {
        $this->bulkManagement   = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService  = $identityService;
        $this->serializer       = $serializer;
        $this->userContext      = $userContext;
        $this->brandHelper      = $helper;
    }

    /**
     * Publish sales rule usage info into the queue
     *
     * @param $attributeCode
     * @param $storeId
     * @param $action
     * @param $productIds
     * @param null $optionId
     *
     * @return void
     * @throws LocalizedException
     */
    public function publish(
        $attributeCode,
        $storeId,
        $action,
        $productIds,
        $optionId = null
    ): void {
        {
            $bulkUuid        = $this->identityService->generateId();
            $bulkDescription = __('Update brand code ' . $attributeCode . ' for ' . count($productIds) . ' selected products');
            $operations      = [];

            if ($attributeCode) {
                $operations[] = $this->makeOperation(
                    'Update brand products with IDs: ' . count($productIds) . ' ',
                    'mpbrand.update.attribute',
                    $attributeCode,
                    $storeId,
                    $action,
                    $productIds,
                    $optionId,
                    $bulkUuid
                );
            }
            if (!empty($operations)) {
                $result = $this->bulkManagement->scheduleBulk(
                    $bulkUuid,
                    $operations,
                    $bulkDescription,
                    $this->userContext->getUserId()
                );
                if (!$result) {
                    throw new LocalizedException(
                        __('Something went wrong while processing the request.')
                    );
                }
            }
        }
    }

    /**
     * Make asynchronous operation
     *
     * @param string $meta
     * @param string $queue
     * @param array $dataToUpdate
     * @param int $storeId
     * @param $action
     * @param $productId
     * @param $optionId
     * @param int $bulkUuid
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface
     */
    private function makeOperation(
        $meta,
        $queue,
        $dataToUpdate,
        $storeId,
        $action,
        $productId,
        $optionId,
        $bulkUuid
    ): OperationInterface {
        $dataToEncode = [
            'meta_information' => $meta,
            'product_ids'      => json_encode($productId),
            'store_id'         => $storeId,
            'option_id'        => $optionId,
            'action'           => $action,
            'attribute_code'   => $dataToUpdate
        ];
        $data         = [
            'data' => [
                'bulk_uuid'       => $bulkUuid,
                'topic_name'      => $queue,
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status'          => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }
}
