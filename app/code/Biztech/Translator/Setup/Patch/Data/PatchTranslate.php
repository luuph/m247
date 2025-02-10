<?php
namespace Biztech\Translator\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PatchTranslate implements DataPatchInterface,PatchRevertableInterface
{
   /** @var ModuleDataSetupInterface */
   private $moduleDataSetup;

   /** @var EavSetupFactory */
   private $eavSetupFactory;

   /**
    * @param ModuleDataSetupInterface $moduleDataSetup
    * @param EavSetupFactory $eavSetupFactory
    */
   private $resourceConfig;
    private $_date;
   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       EavSetupFactory $eavSetupFactory,
       Config $resourceConfig,
       DateTime $datetime
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
       $this->eavSetupFactory = $eavSetupFactory;
       $this->resourceConfig = $resourceConfig;
       $this->_date = $datetime;
    }

   /**
    * {@inheritdoc}
    */
   public function apply()
   {
       /** @var EavSetup $eavSetup */ 
       $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'translated',[
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Translated',
                'input' => 'boolean',
                'class' => '',
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable',
            ]
        );           

        $insSetup = $this->eavSetupFactory->create()->getSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $insSetup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'translated',
            'is_used_in_grid',
            true
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'translated',
            'is_visible_in_grid',
            true
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'translated',
            'is_filterable_in_grid',
            true
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'translated',
            'is_searchable_in_grid',
            true
        );
        $this->resourceConfig->saveConfig('translator/general/module_installed_date', $this->_date->gmtDate() , 'default', 0);
        $this->moduleDataSetup->getConnection()->endSetup();
        return [];
   }

   /**
    * {@inheritdoc}
    */
   public static function getDependencies()
   {
       return [];
   }

   /**
     * @inheritdoc
     */
    public function revert()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            \Magento\Customer\Model\Product::ENTITY,
            'translated'
        );
    }

   /**
    * {@inheritdoc}
    */
   public function getAliases()
   {
       return [];
   }

   /**
   * {@inheritdoc}
   */
   public static function getVersion()
   {
      return '2.1.7';
   }
}