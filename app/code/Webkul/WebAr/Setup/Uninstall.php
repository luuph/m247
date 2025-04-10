<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface as UninstallInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;
    
    /**
     * @var ModuleDataSetupInterface
     */
    private $_mDSetup;
    
    /**
     * Initialize dependencies
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $mDSetup
     * @return void
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $mDSetup
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_mDSetup = $mDSetup;
    }

    /**
     * Uninstall custom attributes
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_mDSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ios_model_file');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'model_file');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'allow_glb_in_child_products');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'model_variant_attribute');
        $installer->endSetup();
    }
}
