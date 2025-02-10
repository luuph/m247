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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Publisher
{
    /**
     * Default value of bunch size for one operation
     */
    private const MESSAGE_BUNCH_SIZE_DEFAULT = 5000;

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
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var int|null
     */
    private $messageBunchSize;

    /**
     * @param Config $moduleConfig
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param UserContextInterface $userContextInterface
     * @param Json $jsonSerializer
     * @param int|null $messageBunchSize
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        UserContextInterface $userContextInterface,
        Json $jsonSerializer,
        ?int $messageBunchSize = null
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->userContext = $userContextInterface;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageBunchSize = $messageBunchSize ?: self::MESSAGE_BUNCH_SIZE_DEFAULT;
    }

    /**
     * Schedule bulk operation
     *
     * @param int $templateId
     * @param array $optionsSave
     * @param array $optionsDelete
     * @param  array $productIdsDelete
     * @return void
     */
    public function execute($templateId, $optionsSave = [], $optionsDelete = [], $productIdsDelete = [])
    {
        $bulkUuid = $this->identityService->generateId();
        $serializedData = $this->jsonSerializer->serialize(
            [
                'template_id' => $templateId,
                'options_save' => $optionsSave,
                'options_delete' => $optionsDelete,
                'product_ids_delete' => $productIdsDelete
            ]
        );
        /** @var OperationInterface $operation */
        $operation = $this->operationFactory->create(
            [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => 'bss_c_o_t.save',
                    'serialized_data' => $serializedData,
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ]
        );
        $userId = $this->userContext->getUserId();
        $this->bulkManagement->scheduleBulk($bulkUuid, [$operation], __('Save custom option template ID: %1', $templateId), $userId);
    }
}
