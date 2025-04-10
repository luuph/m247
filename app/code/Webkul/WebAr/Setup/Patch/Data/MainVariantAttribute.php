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
class MainVariantAttribute implements DataPatchInterface
{
    /**
     * @var AttributeSet
     */
    private $attributeSet;

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
     * Initialize dependencies
     *
     * @param AttributeSet $attributeSet
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceProduct $resourceProduct
     * @return void
     */
    public function __construct(
        AttributeSet $attributeSet,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ResourceProduct $resourceProduct
    ) {
        $this->attributeSet     = $attributeSet;
        $this->moduleDataSetup  = $moduleDataSetup;
        $this->resourceProduct  = $resourceProduct;
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
       
        $note = 'Please select Model Variant Attribute which will responsible for changing the variant in Main GLB';

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'model_variant_attribute',
            [
                "group" => "WebAr Model",
                'type' => 'varchar',
                'label' => 'Model Variant Attribute',
                'input' => 'select',
                'class' => '',
                'source' => \Webkul\WebAr\Model\Config\Source\SuperAttributes::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => 0,
                'filterable' => false,
                'comparable' => false,
                'searchable' => false,
                'unique' => false,
                'visible_on_front' => false,
                'apply_to' => 'configurable',
                'note' => $note,
                "used_in_product_listing" => true,
                "is_html_allowed_on_front" => false,
                "visible_in_advanced_search" => false
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
                "model_variant_attribute"
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
