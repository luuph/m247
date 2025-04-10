<?php

namespace Webkul\MobikulCore\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class Attributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var AttributeSet
     */
    protected $attributeSet;

    /**
     * @var ResourceProduct
     */
    protected $resourceProduct;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemFile;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @param Config $eavConfig
     * @param AttributeSet $attributeSet
     * @param ResourceProduct $resourceProduct
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param AttributeSetFactory $attributeSetFactory
     * @param \Magento\Framework\App\State $state
     * @param ModuleDataSetupInterface $moduleDataSetup
     */

    public function __construct(
        Config $eavConfig,
        AttributeSet $attributeSet,
        ResourceProduct $resourceProduct,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Framework\App\State $state,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->reader               = $reader;
        $this->eavConfig            = $eavConfig;
        $this->fileSystem           = $fileSystem;
        $this->attributeSet         = $attributeSet;
        $this->filesystemFile       = $filesystemFile;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->resourceProduct      = $resourceProduct;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->state                = $state;
        $this->moduleDataSetup      = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(["setup"=>$this->moduleDataSetup]);
        try {
            $this->state->getAreaCode();
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "as_featured",
            [
                "group" => "Product Details",
                "used_in_product_listing" => true,
                "filterable" => false,
                "input" => "boolean",
                "label" => "Is featured for Mobikul ?",
                "global" => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                "comparable" => false,
                "searchable" => false,
                "user_defined" => true,
                "visible_on_front" => false,
                "visible_in_advanced_search" => false,
                "is_html_allowed_on_front" => false,
                "required" => false,
                "unique" => false,
                "is_configurable" => false
            ]
        );
        $entityType = $this->resourceProduct->getEntityType();
        $attributeSetCollection = $this->attributeSet->setEntityTypeFilter($entityType);
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToSet(
                "catalog_product",
                $attributeSet->getAttributeSetName(),
                "General",
                "as_featured"
            );
        }
        $eavSetup = $this->eavSetupFactory->create(["setup" => $this->moduleDataSetup]);
        $this->moveDirToMediaDir();
        $groupName = "Mobikul Configuration";
        $entityTypeId = $eavSetup->getEntityTypeId("catalog_product");
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 2);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            $attributeId = $eavSetup->getAttributeId($entityTypeId, "as_featured");
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
        }
        $this->eavConfig->getAttribute("catalog_product", "as_featured")->setSortOrder(1)->save();
        $typeSource = \Webkul\MobikulCore\Model\Config\Source\ArOptions::class;

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_type",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "backend" => "",
                "frontend" => "",
                "label" => "Ar Model Type",
                "input" => "select",
                "class" => "",
                "source" => $typeSource,
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => false,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "used_in_product_listing" => false,
                "unique" => false,
                "sort_order" => 2,
                "apply_to" => "simple,configurable",
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_2d_file",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR 2-D Model File",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 3,
                "note" => "Allowed file type: png."
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_model_file_android",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR Model File For Android",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 4,
                "note" => "Allowed file type: glb."
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_model_file_ios",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR Model File For Ios",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 5,
                "note" => "Allowed file type: usdz."
            ]
        );
        
        $eavSetup->addAttribute('customer_address', 'address_title', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Address Title',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
        ]);
       
        $addressAttribute = $this->eavConfig->getAttribute('customer_address', 'address_title');

        $addressAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        );
        $addressAttribute->save();

        $entityType = $this->resourceProduct->getEntityType();
        $attributeSetCollection = $this->attributeSet->setEntityTypeFilter($entityType);
        foreach ($attributeSetCollection as $attributeSet) {
            $attributes = [
                "ar_type",
                "ar_model_file_android",
                "ar_model_file_ios",
                "ar_texture_files"
            ];
            foreach ($attributes as $attribute) {
                $eavSetup->addAttributeToSet(
                    "catalog_product",
                    $attributeSet->getAttributeSetName(),
                    "General",
                    $attribute
                );
            }
        }
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
    public function getAliases()
    {
        /**
         * This internal method, that means that some patches with time can change their names,
         */
        return [];
    }

    /**
     * Function to move Media to media directory
     *
     * @return void
     */
    protected function moveDirToMediaDir()
    {
        $mediaSplashScreenImageFullPath = $this->fileSystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath("mobikul/splashscreen");
        if (!$this->filesystemFile->fileExists($mediaSplashScreenImageFullPath)) {
            $this->filesystemFile->mkdir($mediaSplashScreenImageFullPath, 0777, true);
            $splashScreenImage = $this->reader->getModuleDir(
                "",
                "Webkul_MobikulCore"
            )."/view/base/web/images/mobikul/splashscreen/splashscreen.png";
            $this->filesystemFile->cp($splashScreenImage, $mediaSplashScreenImageFullPath."/splashscreen.png");
        }
    }

    /**
     * Revert function
     *
     * @return void
     */
    public function revert()
    {
        $eavSetup = $this->eavSetupFactory->create(["setup"=>$this->moduleDataSetup]);
        try {
            $this->state->getAreaCode();
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "as_featured");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "ar_type");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "ar_2d_file");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "ar_model_file_android");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "ar_model_file_ios");
        $eavSetup->removeAttribute('customer_address', 'address_title');
    }
}
