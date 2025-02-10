<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('newsletter_template'),
            'orig_template_text',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '1M',
                'comment' => 'Original template text'
            ]
        );

        $installer->getConnection()->changeColumn(
            $installer->getTable('newsletter_template'),
            'template_text',
            'template_text',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '1M',
                'comment' => 'Template Text'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('newsletter_template'),
            'is_mtemail',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 1,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Flag: is template build with mteditor'
            ]
        );

        $installer->getConnection()->changeColumn(
            $installer->getTable('newsletter_queue'),
            'newsletter_text',
            'newsletter_text',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '1M',
                'comment' => 'Newsletter Text'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('newsletter_queue'),
            'is_mtemail',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 1,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Flag: is template build with mteditor'
            ]
        );

        $installer->endSetup();
    }

    public function createTable($installer, $tableName, $columns)
    {
        $db = $installer->getConnection();
        $table = $db->newTable($tableName);
        
        foreach ($columns as $name => $info) {
            $options = [];
            if (isset($info['options'])) {
                $options = $info['options'];
            }

            if (isset($info['primary']) && $info['primary'] == 1) {
                $options = ['identity' => true, 'nullable' => false, 'primary' => true];
            }

            $table->addColumn(
                $name,
                $info['type'],
                $info['length'],
                $options,
                $name
            );

            if (isset($info['index'])) {
                $table->addIndex(
                    $installer->getIdxName($tableName, [$name]),
                    [$name]
                );
            }

            if (isset($info['foreign_key'])) {
                $table->addForeignKey(
                    $installer->getFkName($tableName, $name, $info['foreign_key'][0], $info['foreign_key'][1]),
                    $name,
                    $installer->getTable($info['foreign_key'][0]),
                    $info['foreign_key'][1],
                    Table::ACTION_SET_NULL
                );
            }
        }

        $db->createTable($table);
    }
}
