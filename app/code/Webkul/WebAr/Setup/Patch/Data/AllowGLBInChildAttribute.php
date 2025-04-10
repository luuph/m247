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
class AllowGLBInChildAttribute implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ResourceProduct
     */
    private $resourceProduct;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AttributeSet
     */
    private $attributeSet;

    /**
     * Initialize dependencies
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSet $attributeSet
     * @param ResourceProduct $resourceProduct
     * @return void
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSet $attributeSet,
        ResourceProduct $resourceProduct
    ) {
        $this->attributeSet    = $attributeSet;
        $this->moduleDataSetup  = $moduleDataSetup;
        $this->resourceProduct = $resourceProduct;
        $this->eavSetupFactory  = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
       
        $note = 'If enabled, then you need to upload GLB files for associated products only'
        .' and if disabled, then upload the GLB (which has configured all variants)'
        .' file for the main product only.';

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allow_glb_in_child_products',
            [
                "group" => "WebAr Model",
                'type' => 'int',
                'label' => 'Allow GLB In Child Products?',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'configurable',
                'note' => $note,
                "used_in_product_listing" => true,
                "visible_in_advanced_search" => false,
                "is_html_allowed_on_front" => false
            ]
        );
       
        $entityType = $this->resourceProduct->getEntityType();
        $attributeSetCollection = $this->attributeSet->setEntityTypeFilter(
            $entityType
        );
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToSet(
                "catalog_product",
                $attributeSet->getAttributeSetName(),
                "WebAr Model",
                "allow_glb_in_child_products"
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
