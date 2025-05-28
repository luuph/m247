<?php

namespace MagePsycho\RegionCityPro\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;
use MagePsycho\RegionCityPro\Model\ResourceModel\Address\Attribute\Backend\City as CityBackend;
use MagePsycho\RegionCityPro\Model\ResourceModel\Address\Attribute\Source\City as CitySource;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddCityIdAddressAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        if ($this->checkIfAddressAttributeExists(CityInterface::ID)) {
            return;
        }
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            CityInterface::ID,
            [
                'label'                 => 'City',
                'type'                  => 'int',
                'input'                 => 'select',
                'required'              => false,
                'visible'               => true,
                'system'                => false,
                'user_defined'          => true,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'source'                => CitySource::class,
                'backend'               => CityBackend::class,
                'sort_order'            => 82,
                'position'              => 82,
            ]
        );

        $attributeSetId = $customerSetup->getEavConfig()
            ->getEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)
            ->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $attribute = $customerSetup->getEavConfig()
            ->clear()
            ->getAttribute(
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                CityInterface::ID
            )
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                    'adminhtml_customer_address',
                    'customer_register_address',
                    'customer_address_edit'
                ],
            ]);
        $attribute->save();
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

    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    private function checkIfAddressAttributeExists($attributeCode)
    {
        try {
            return (bool) $this->attributeRepository->get(
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                $attributeCode
            );
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
