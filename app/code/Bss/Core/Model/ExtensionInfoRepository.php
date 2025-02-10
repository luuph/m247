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
 * @package    Bss_Core
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Core\Model;

use Bss\Core\Api\ExtensionInfoRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Bss\Core\Model\ResourceModel\ExtensionInfo\CollectionFactory as ExtensionInfoCollectionFactory;
use Bss\Core\Helper\Api;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * ExtensionInfo class
 */
class ExtensionInfoRepository implements ExtensionInfoRepositoryInterface
{
    /**
     * Table name
     */
    const TABLE_NAME = 'bss_core_api_data_extension_info';

    /**
     * Time check
     */
    const ONE_DAY = 86400;

    /**
     * Date time
     *
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Promtion collection
     *
     * @var ExtensionInfoCollectionFactory
     */
    protected $extensionCollectionFactory;

    /**
     * Api helper
     *
     * @var Api
     */
    protected $apiHelper;

    /**
     * Connection
     *
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Serializer
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Contruct
     *
     * @param Api $apiHelper
     * @param PromotionCollectionFactory $promotionCollectionFactory
     * @param DateTime $dateTime
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Api $apiHelper,
        ExtensionInfoCollectionFactory $extensionCollectionFactory,
        DateTime $dateTime,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->extensionCollectionFactory = $extensionCollectionFactory;
        $this->dateTime = $dateTime;
        $this->apiHelper = $apiHelper;
        $this->resourceConnection = $resourceConnection;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionInfo($type)
    {
        $extensionInfoCollection = $this->extensionCollectionFactory->create();
        $extensionInfo = $extensionInfoCollection->addFieldToFilter('type', $type)->getFirstItem();

        $currentTimestamp = $this->dateTime->gmtTimestamp();

        if ($extensionInfo  && $extensionInfo->getLastUpdated()) {
            $lastUpdatedTimestamp = strtotime($extensionInfo->getLastUpdated() ?? '');

            if (($currentTimestamp - $lastUpdatedTimestamp) < self::ONE_DAY) {
                return $this->jsonHelper->jsonDecode($extensionInfo->getResponseData());
            }
        }

        try {
            $newData = $this->fetchDataFromApi($type);
        } catch (NoSuchEntityException $e) {
            return $extensionInfo ? $this->jsonHelper->jsonDecode($extensionInfo->getResponseData()) : [];
        }

        $this->saveDataToCache($newData, $type);

        return $newData;
    }

    /**
     * Call Api get Data
     *
     * @return void
     */
    private function fetchDataFromApi($type)
    {
        return $type == 'promotion' ? $this->apiHelper->getPromotions() : $this->apiHelper->getModules();
    }

    /**
     * Save Data
     *
     * @param mixed $data
     * @return void
     */
    private function saveDataToCache($data, $type)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TABLE_NAME);

        $connection->delete($tableName, ['type = ?' => $type]);

        $connection->insert($tableName, [
            'type' => $type,
            'response_data' => $this->jsonHelper->jsonEncode($data),
            'last_updated' => $this->dateTime->gmtDate()
        ]);
    }
}
