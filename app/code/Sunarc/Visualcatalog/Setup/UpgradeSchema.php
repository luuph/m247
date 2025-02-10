<?php

namespace Sunarc\Visualcatalog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
  private $_categoryFactory;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
               $this->_categoryFactory = $categoryFactory;
    }
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('catalog_category_product');

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $sql = "Select DISTINCT category_id FROM " . $tableName;
                $result = $connection->fetchAll($sql);
                foreach ($result as $res) {
                    $category = $this->_categoryFactory->create()->load($res);
                     $product_list = $category->getProductCollection()->addAttributeToSort('position', 'ASC');
        $product_list = $product_list->getData();

        $products = array();
        foreach($product_list as $k1 => $product){
            $products[$product['entity_id']]= $k1+1;
        }
            $category->setPostedProducts($products);
            $category->save();
                  
                }
            }
        }

        $setup->endSetup();
    }
}
