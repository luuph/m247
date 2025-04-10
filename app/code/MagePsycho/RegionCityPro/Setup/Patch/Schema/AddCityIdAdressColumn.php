<?php

namespace MagePsycho\RegionCityPro\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddCityIdAdressColumn implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $connection = $this->schemaSetup->getConnection();
        $quoteAddressTable =  $this->schemaSetup->getTable('quote_address');
        if (!$connection->tableColumnExists($quoteAddressTable, CityInterface::ID)) {
            $connection->addColumn(
                $quoteAddressTable,
                CityInterface::ID,
                [
                    'type' => Table::TYPE_INTEGER,
                    'comment' => 'City ID'
                ]
            );
        }

        $salesOrderAddressTable =  $this->schemaSetup->getTable('sales_order_address');
        if (!$connection->tableColumnExists($salesOrderAddressTable, CityInterface::ID)) {
            $connection->addColumn(
                $salesOrderAddressTable,
                CityInterface::ID,
                [
                    'type' => Table::TYPE_INTEGER,
                    'comment' => 'City ID'
                ]
            );
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.5';
    }
}
