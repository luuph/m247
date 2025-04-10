<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Setup\Patch\Data;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Webkul Walletsystem Class
 */
class CreateAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var Installer
     */
    protected $productType = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $catalogProductTypeManager;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Framework\File\Mime
     */
    protected $mime;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * Initialize dependencies
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\App\State $appstate
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\File\Mime $mime
     * @param \Magento\Framework\Module\Dir\Reader $reader
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Catalog\Model\Product $productModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $appstate,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\File\Mime $mime,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->appState = $appstate;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->catalogProductTypeManager = $catalogProductTypeManager;
        $this->file = $file;
        $this->mime = $mime;
        $this->reader = $reader;
    }

    /**
     * Apply
     */
    public function apply()
    {
        $this->eavConfig->clear();

        $product = $this->productFactory->create();
        $store = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $attributeSetId = $this->productModel->getDefaultAttributeSetId();

        $mageProduct = $this->productFactory->create();
        $mageProduct->setAttributeSetId($attributeSetId);
        $mageProduct->setTypeId($this->productType);
        $mageProduct->setStoreId($store);
        $productId = $this->productFactory->create()->getIdBySku(\Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU);

        if (!$productId) {
            $requestData = [
                'product' => [
                    'name' => 'Wallet Amount',
                    'attribute_set_id' => $attributeSetId,
                    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                    'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    'sku' => \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU,
                    'tax_class_id' => 0,
                    'price' => 0,
                    'description' => 'wallet amount',
                    'short_description' => 'wallet amount',
                    'stock_data' => [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 0,
                        'is_decimal_divided' => 0
                    ],
                    'quantity_and_stock_status' => [
                        'qty' => 1,
                        'is_in_stock' => 1
                    ]
                ]
            ];

            $catalogProduct = $this->productInitialize($mageProduct, $requestData);
            $this->catalogProductTypeManager->processProduct($catalogProduct);
            if ($catalogProduct->getSpecialPrice() == '') {
                $catalogProduct->setSpecialPrice(null);
                $catalogProduct->getResource()->saveAttribute($catalogProduct, 'special_price');
            }
            $this->moveDirToMediaDir();
            $imagePath = $this->getViewFileUrl().'walletsystem/wallet.png';

            $catalogProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);

            $this->appState->emulateAreaCode(
                "frontend",
                [$this, "saveProduct"],
                [$catalogProduct]
            );
        }
            $eavSetup= $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'wallet_credit_based_on',
                [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Product Wallet Credit Amount Based On', /* Lablel of your attribute*/
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\Walletsystem\Model\Config\Source\ProductattrOptions::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'note' => 'Product Credit amount based on rules amount or on wallet credit amount',
                    'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
                ]
            );
            $eavSetup= $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'wallet_cash_back');
            $eavSetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'wallet_cash_back',
                [
                    'type' => 'decimal',
                    'input' => 'price',
                    'required' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'Product Details',
                    'used_in_product_listing' => true,
                    'visible_on_front' => true,
                    'visible' => true,
                    'user_defined' => true,
                    'unique' => false,
                    'is_configurable' => false,
                    'used_for_promo_rules' => true,
                    'backend' => '',
                    'default' => 0,
                    'frontend' => '',
                    'frontend_class'=>'validate-zero-or-greater',
                    'label' =>  'Wallet Cash Back',
                    'note' => 'Product wise credit amount.',
                    'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
                ]
            );
    }

    /**
     * Function
     *
     * @param object $catalogProduct
     */
    public function saveProduct($catalogProduct)
    {
        $catalogProduct->save();
    }

    /**
     * GetViewFileUrl function is used to get file viewl url
     *
     * @return string
     */
    public function getViewFileUrl()
    {
        return $this->filesystem
                    ->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath();
    }

    /**
     * Product Initialize function
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array                          $requestData
     * @return \Magento\Catalog\Model\Product
     */
    private function productInitialize(\Magento\Catalog\Model\Product $catalogProduct, $requestData)
    {
        $requestProductData = $requestData['product'];
        $requestProductData['product_has_weight'] = 0;
        $catalogProduct->addData($requestProductData);
        $websiteIds = [];
        $allWebsites = $this->storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $websiteIds[] = $website->getId();
        }
        $catalogProduct->setWebsiteIds($websiteIds);
        return $catalogProduct;
    }

     /**
      * MoveDirToMediaDir move directories to media
      */
    private function moveDirToMediaDir()
    {
        $reader = $this->reader;
        $filesystem = $this->filesystem;
        $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
        $smpleFilePath = $filesystem->getDirectoryRead($type)
            ->getAbsolutePath().'walletsystem/';

        $modulePath = $reader->getModuleDir('', 'Webkul_Walletsystem');
        $mediaFile = $modulePath.'/view/frontend/web/images/wallet.png';
        try {
            $this->mime->getMimeType($smpleFilePath);
        } catch (\Exception $e) {
            $this->file->createDirectory($smpleFilePath, 0777);
        }
        $filePath = $smpleFilePath.'wallet.png';
        try {
            $this->mime->getMimeType($filePath);
        } catch (\Exception $e) {
            if ($this->mime->getMimeType($mediaFile)) {
                $this->file->copy($mediaFile, $filePath);
            }
        }
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
    /**
     * Rever Patch
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'wallet_credit_based_on'
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'wallet_cash_back'
        );
        $this->moduleDataSetup->getConnection()->startSetup();
        //Here should go code that will revert all operations from `apply` method
        //Please note, that some operations, like removing data from column, that is in role of foreign key reference
        //is dangerous, because it can trigger ON DELETE statement
        $this->moduleDataSetup->getConnection()->endSetup();
        $this->moduleDataSetup->getConnection()->endSetup();
    }
}
