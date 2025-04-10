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
namespace Webkul\WebAr\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ArModelAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ResourceProduct
     */
    private $_resourceProduct;

    /**
     * @var AttributeSet
     */
    private $_attributeSet;

    /**
     * Initialize dependencies
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceProduct $resourceProduct
     * @param AttributeSet $attributeSet
     * @return void
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ResourceProduct $resourceProduct,
        AttributeSet $attributeSet
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_resourceProduct = $resourceProduct;
        $this->_attributeSet    = $attributeSet;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
       
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'model_file',
            [
                'type' => 'varchar',
                'group' => 'WebAr Model',
                'label' => 'AR model File (Android)',
                'input' => 'file',
                'backend' => \Webkul\WebAr\Model\Product\Attribute\Backend\File::class,
                'frontend' => '',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,virtual,configurable,downloadable,grouped,bundle',
                'used_in_product_listing' => false,
                'note' => 'allowed extension .glb'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'ios_model_file',
            [
                'type' => 'varchar',
                'group' => 'WebAr Model',
                'label' => 'AR model File (ios)',
                'input' => 'file',
                'backend' => \Webkul\WebAr\Model\Product\Attribute\Backend\IosFile::class,
                'frontend' => '',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,virtual,configurable,downloadable,grouped,bundle',
                'used_in_product_listing' => false,
                'note' => 'allowed extension .usdz'
            ]
        );
        $entityType = $this->_resourceProduct->getEntityType();
        $attributeSetCollection = $this->_attributeSet->setEntityTypeFilter($entityType);
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToSet(
                "catalog_product",
                $attributeSet->getAttributeSetName(),
                "WebAr Model",
                "model_file"
            );
            $eavSetup->addAttributeToSet(
                "catalog_product",
                $attributeSet->getAttributeSetName(),
                "WebAr Model",
                "ios_model_file"
            );
        }
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
}
