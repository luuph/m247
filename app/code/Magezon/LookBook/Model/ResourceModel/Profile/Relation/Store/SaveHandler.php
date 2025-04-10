<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://magezon.com)
 */

namespace Magezon\LookBook\Model\ResourceModel\Profile\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magezon\LookBook\Api\Data\ProfileInterface;
use Magezon\LookBook\Model\ResourceModel\Profile;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Profile
     */
    protected $resourceProfile;

    /**
     * @param MetadataPool $metadataPool
     * @param Profile         $resourceProfile
     */
    public function __construct(
        MetadataPool $metadataPool,
        Profile $resourceProfile
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceProfile = $resourceProfile;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->hasData('store_id')) {
            $entityMetadata = $this->metadataPool->getMetadata(ProfileInterface::class);
            $linkField      = $entityMetadata->getLinkField();
            $connection     = $entityMetadata->getEntityConnection();
            $oldStores      = $this->resourceProfile->lookupStoreIds((int)$entity->getId());
            $newStores      = (array)$entity->getStoreId();
            $table          = $this->resourceProfile->getTable('mgz_lookbook_profile_store');
            $delete         = array_diff($oldStores, $newStores);
            if ($delete) {
                $where = [
                    $linkField . ' = ?' => (int)$entity->getData($linkField),
                    'store_id IN (?)' => $delete
                ];
                $connection->delete($table, $where);
            }

            $insert = array_diff($newStores, $oldStores);
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = [
                        $linkField => (int)$entity->getData($linkField),
                        'store_id' => (int)$storeId
                    ];
                }
                $connection->insertMultiple($table, $data);
            }
        }
        return $entity;
    }
}
