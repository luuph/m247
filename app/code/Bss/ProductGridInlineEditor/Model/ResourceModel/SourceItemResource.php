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
 * @package    Bss_ProductGridInlineEditor
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductGridInlineEditor\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Product grid inline edit model
 *
 */
class SourceItemResource
{
    const INVENTORY_ITEM_TABLE = 'inventory_source_item';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * Get Source Item Data
     *
     * @param $productSku
     * @param $sourceCode
     * @return mixed
     */
    public function getSourceItemData($productSku, $sourceCode)
    {
        $inventoryItemTable = $this->resourceConnection->getTableName(self::INVENTORY_ITEM_TABLE);

        $select = $this->connection->select()
            ->from($inventoryItemTable)
            ->where(SourceItemConfigurationInterface::SOURCE_CODE . ' = ?', $sourceCode)
            ->where(SourceItemConfigurationInterface::SKU . ' = ?', $productSku);

        return $this->connection->fetchRow($select);
    }
}
