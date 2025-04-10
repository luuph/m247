<?php
namespace Magecomp\Whatsappultimate\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Setup\CustomerSetupFactory;

class AddWptermAttribute implements DataPatchInterface
{
    private $moduleDataSetup;

    private $customerSetupFactory;

    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'wpterms',
            [
                'type' => 'int',
                'label' => 'Wp terms',
                'frontend_input' => 'text',
                'required' => false,
                'visible' => true,
                'system'=> 0,
                'default' => '1',
                'position' => 80,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false
            ]
        );

        
        $wpattribute = $customerSetup->getEavConfig()->getAttribute('customer', 'wpterms');
        $used_in_forms_wp[]="customer_account_edit";


        $wpattribute->setData('used_in_forms', $used_in_forms_wp);
        $wpattribute->save();
    }

   /**
    * {@inheritdoc}
    */
    public static function getDependencies()
    {
        return [];
    }

   /**
    * {@inheritdoc}
    */
    public function getAliases()
    {
        return [];
    }
}
