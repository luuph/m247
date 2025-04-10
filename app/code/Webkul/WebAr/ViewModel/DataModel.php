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
namespace Webkul\WebAr\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class DataModel implements ArgumentInterface
{
    /**
     * Constant values of Width and Height for Associated Products' thumbnail images
     */
    public const CHILD_THUMB_IMG_WIDTH = 200;
    public const CHILD_THUMB_IMG_HEIGHT = 200;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\WebAr\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Block\Product\View\Gallery
     */
    protected $productGallery;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $productImgHelper;

    /**
     * @var string
     */
    protected $firstModelUrl;

    /**
     * @var string
     */
    protected $firstIosModelUrl;

    /**
     * @var int
     */
    protected $firstAssociatedProductId = 0;

    /**
     * @var array
     */
    protected $associatedProductThumbs = [];

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var int
     */
    protected $isAssociatedProductHasImgFlag = 0;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\WebAr\Logger\Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Helper\Image $productImgHelper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Block\Product\View\Gallery $productGallery
     * @return void
     */
    public function __construct(
        \Webkul\WebAr\Logger\Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Image $productImgHelper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\View\Gallery $productGallery
    ) {
        $this->logger = $logger;
        $this->assetRepo = $assetRepo;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->productGallery = $productGallery;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->productImgHelper = $productImgHelper;
    }

    /**
     * Get Product Image Helper
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getProductImageHelper()
    {
        return $this->productImgHelper;
    }

    /**
     * Get Json Helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Get Store Manager
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get Media URL
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get Default/First Associated Product Id
     *
     * @return int
     */
    public function getFirstAssociatedProductId()
    {
        return $this->firstAssociatedProductId;
    }

    /**
     * Get Default Associated 3D Model URL
     *
     * @return string
     */
    public function getDefault3DModelUrl()
    {
        return $this->firstModelUrl;
    }

    /**
     * Get Default Associated 3D iOS Model URL
     *
     * @return string
     */
    public function getDefault3DIosModelUrl()
    {
        return $this->firstIosModelUrl;
    }

    /**
     * Get module status from admin configuration
     *
     * @return int
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            'webar/general_settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return 3D model file name
     *
     * @param int $productId
     * @return array
     */
    public function get3dModelFile($productId = 0)
    {
        $data = [];
        try {
            if ($productId) {
                $storeId = $this->getStoreManager()->getStore()->getId();
                $product = $this->productFactory->create()
                    ->setStoreId($storeId)
                    ->load($productId);
                if (!empty($product->getId())) {
                    if (!empty($product->getModelFile())) {
                        $data['model_file'] = $product->getModelFile();
                    }
                    if (!empty($product->getIosModelFile())) {
                        $data['ios_file'] = $product->getIosModelFile();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "get3dModelFile method error:".$e->getMessage()
            );
        }
        return $data;
    }

    /**
     * Return product data
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product|bool
     */
    public function getProductData($productId = 0)
    {
        try {
            if ($productId) {
                $storeId = $this->getStoreManager()->getStore()->getId();
                $product = $this->productFactory->create()
                    ->setStoreId($storeId)
                    ->load($productId);
                if (!empty($product->getId())) {
                    return $product;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "getProductData method error:".$e->getMessage()
            );
        }
        return false;
    }
    
    /**
     * Get Associated Product Thumbnails
     *
     * @return array
     */
    public function getAssociatedProductThumbs()
    {
        return $this->associatedProductThumbs ?? [];
    }

    /**
     * Get $isAssociatedProductHasImgFlag value
     *
     * @return int
     */
    public function getIsAssociatedProductHasImgFlag()
    {
        return $this->isAssociatedProductHasImgFlag;
    }

    /**
     * Set $isAssociatedProductHasImgFlag value
     *
     * @param \Magento\Catalog\Model\Product $child
     * @return void
     */
    private function setIsAssociatedProductHasImgFlag($child)
    {
        try {
            $childProductImages = $child->getMediaGalleryImages();
            $imageCount = $childProductImages->count();
            if ($imageCount > 0) {
                $this->isAssociatedProductHasImgFlag = 1;
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "setIsAssociatedProductHasImgFlag method error:".$e->getMessage()
            );
        }
    }

    /**
     * Return texture files array
     *
     * @param \Magento\Catalog\Model\Product $configProduct
     * @return array
     */
    public function getAssociatedProductModels($configProduct)
    {
        $output = [];
        $mediaUrl = $this->getMediaUrl();

        if ($configProduct) {
            try {
                $children = $configProduct->getTypeInstance()
                    ->getUsedProducts($configProduct);
    
                $productCount = $productIosModelCount = 1;

                foreach ($children as $index => $child) {
                    $check3dEnabled = $this->get3dModelFile(
                        $child->getId()
                    );
                    
                    $modelUrl = $iosModelUrl = $thumbnailUrl = "";
                    
                    //Set Product Main Image Url and Thumbnail URLs
                    $thumbnailUrl = $mediaUrl.'catalog/product/'.$child->getData("image") ?? "";
                    $fotoramaThumbnailUrl = $this->productImgHelper->init(
                        $child,
                        'product_page_image_small'
                    )->setImageFile($child->getFile())
                        ->resize(self::CHILD_THUMB_IMG_WIDTH, self::CHILD_THUMB_IMG_HEIGHT)
                        ->getUrl();
                    $this->associatedProductThumbs[$child->getId()] = $fotoramaThumbnailUrl;
                    /////

                    //Set isAssociatedProductHasImgFlag Value
                    if ($this->isAssociatedProductHasImgFlag == 0) {
                        $this->setIsAssociatedProductHasImgFlag($child);
                    }

                    if (!empty($check3dEnabled['model_file'])) {
                        $modelUrl =  $mediaUrl.'catalog/product/glbmodels/'
                            .$check3dEnabled['model_file'];
                    }
                    if (!empty($check3dEnabled['ios_file'])) {
                        $iosModelUrl =  $mediaUrl.'catalog/product/glbmodels/'
                            .'ios/'.$check3dEnabled['ios_file'];
                    }

                    if ($productCount == 1 && $modelUrl != "") {
                        $this->firstModelUrl = $modelUrl ?? "";
                        $productCount++;
                    }
                    if ($productIosModelCount == 1 && $iosModelUrl != "") {
                        $this->firstIosModelUrl = $iosModelUrl ?? "";
                        $productIosModelCount++;
                    }
                    if ($index == 0) {
                        $this->firstAssociatedProductId = $child->getId();
                    }

                    if ($modelUrl == "") {
                        $modelUrl = $this->assetRepo->getUrl(
                            "Webkul_WebAr::images/WkDefault3dModel/3DModelNotExists.glb"
                        );
                    }
                    
                    $data = [
                        'modelUrl' => $modelUrl,
                        'thumbnail' => $thumbnailUrl,
                        'iosModelUrl' => $iosModelUrl
                    ];
                    $output[$child->getId()] = $data;
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    "getAssociatedProductModels method error:".$e->getMessage()
                );
            }
        }
       
        return $output;
    }

    /**
     * Get model-viewer attribute value from admin configuration
     *
     * @param string $attrField
     * @return mixed
     */
    public function getModelViewerAttributeValue($attrField = "")
    {
        return $this->scopeConfig->getValue(
            $attrField,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Can Show Other Images with 3D Model
     *
     * @return bool
     */
    public function canShowOtherImages()
    {
        return (bool)$this->scopeConfig->getValue(
            'webar/general_settings/show_other_images',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Product's Images Collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getGalleryImages()
    {
        return $this->productGallery->getGalleryImages();
    }

    /**
     * Is product main image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isMainImage($image)
    {
        return $this->productGallery->isMainImage($image);
    }

    /**
     * Get Lighting And Skybox Attributes Configurations
     *
     * @return array
     */
    public function getLightingAndSkyboxAttributesConfig()
    {
        $config = [
            "applySkyImg" => (int)$this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/apply_skybox'
            ) ?? 0,
            "applyEnvImg" => (int)$this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/apply_env'
            ) ?? 0,
            "envImg" => $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/environment_image'
            ),
            "shadowSoftness" => $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/shadow_softness'
            ),
            "envImgUrl" => $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/environment_image_url'
            ) ?? "",
            "modelShadowIntensity" => $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/shadow_intensity'
            ),
            "modelExposure" => $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/exposure'
            )
        ];
        return $config;
    }

    /**
     * Get Staging And Cameras Attributes Configurations
     *
     * @return array
     */
    public function getStagingAndCamerasAttributesConfig()
    {
        $config = [
            "interpolationDecay" => $this->getModelViewerAttributeValue(
                'webar/attribute_settings/interpolation_decay'
            ),
            "autoRotateDelay" => $this->getModelViewerAttributeValue(
                'webar/attribute_settings/auto_rotate_delay'
            ) ?? '0',
            "autoRotate" => (int)$this->getModelViewerAttributeValue(
                'webar/attribute_settings/auto_rotate'
            ) ?? 1,
            "touchAction" => $this->getModelViewerAttributeValue(
                'webar/attribute_settings/touch_action'
            ) ?? 'pan-y',
            "disableZoom" => (int)$this->getModelViewerAttributeValue(
                'webar/attribute_settings/disable_zoom'
            ) ?? 0,
            "disableTap" => (int)$this->getModelViewerAttributeValue(
                'webar/attribute_settings/disable_tap'
            ) ?? 0,
        ];
        return $config;
    }

    /**
     * Get Image Attributes Configurations
     *
     * @return array
     */
    public function getImageAttributesConfig()
    {
        $imgConfigUrl = [];

        try {
            $skyImgDirPath = '';
            $posterImgDirPath = '';
            $modelThumbnail = '';
            $mediaUrl = $this->getMediaUrl();

            $skyImg = $this->getModelViewerAttributeValue(
                'webar/lighting_attribute_settings/skybox_image'
            );
            $posterImg = $this->getModelViewerAttributeValue(
                'webar/loading_attribute_settings/poster'
            );
            $thumbImg = $this->getModelViewerAttributeValue(
                'webar/general_settings/custom_thumbnail'
            );

            if (!empty($skyImg)) {
                $skyImgDirPath = $mediaUrl.'Wk3dModelSkyboxImg/'.$skyImg;
            }
            if (!empty($posterImg)) {
                $posterImgDirPath = $mediaUrl.'Wk3dModelPosters/'.$posterImg;
            }
            if (!empty($thumbImg)) {
                $modelThumbnail = $mediaUrl.'Wk3dModelPosters/'.$thumbImg;
            }
            $imgConfigUrl = [
                "skyImgDirPath" => $skyImgDirPath,
                "posterImgDirPath" => $posterImgDirPath,
                "modelThumbnail" => $modelThumbnail
            ];
        } catch (\Exception $e) {
            $this->logger->error(
                "getImageAttributesConfig method error:".$e->getMessage()
            );
        }
        return $imgConfigUrl;
    }

    /**
     * Get Attribute Configuration Json
     *
     * @param array $lightAttrs
     * @param array $cameraAttrs
     * @param array $images
     * @param string $loading
     * @return string
     */
    public function getAttributeConfigJson(
        $lightAttrs = [],
        $cameraAttrs = [],
        $images = [],
        $loading = 'auto'
    ) {
        $attrConfigJson = '{}';
        try {
            ///Set autoRotateDelay 0///
            if (!empty($cameraAttrs)) {
                if ($cameraAttrs["autoRotate"] == 0 && $cameraAttrs["interpolationDecay"] != '') {
                    $cameraAttrs["autoRotateDelay"] = 0;
                }
            }
            if (!empty($cameraAttrs) && !empty($lightAttrs) && !empty($images)) {
                $attributeConfigs = [
                    "auto_rotate" => $cameraAttrs["autoRotate"],
                    "auto_rotate_delay" => $cameraAttrs["autoRotateDelay"],
                    "interpolation_decay" => $cameraAttrs["interpolationDecay"] ?? '',
                    "touch_action" => strtolower($cameraAttrs["touchAction"]),
                    "disable_zoom" => $cameraAttrs["disableZoom"],
                    "disable_tap" => $cameraAttrs["disableTap"],
                    "loading" => $loading,
                    "poster" => $images["posterImgDirPath"],
                    "model_thumbnail" => $images["modelThumbnail"],
                    "apply_skybox_image" => $lightAttrs["applySkyImg"],
                    "skybox_image" => $images["skyImgDirPath"],
                    "apply_environment_image" => $lightAttrs["applyEnvImg"],
                    "environment_image" => $lightAttrs["envImg"] ?? '',
                    "environment_image_url" => $lightAttrs["envImgUrl"] ?? '',
                    "shadow_intensity" => $lightAttrs["modelShadowIntensity"] ?? '-1',
                    "shadow_softness" => $lightAttrs["shadowSoftness"] ?? '-1',
                    "exposure" => $lightAttrs["modelExposure"] ?? '-1'
                ];
    
                $attrConfigJson = $this->getJsonHelper()->jsonEncode(
                    $attributeConfigs ?? []
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "getAttributeConfigJson method error:".$e->getMessage()
            );
        }
        return $attrConfigJson;
    }

    /**
     * Get AR Button Configuration
     *
     * @return array
     */
    public function getARButtonConfig()
    {
        $btnConfig = [];
        $showCustomButton = 0;
        $buttonText = __('View in your Space');

        $buttonType = $this->getModelViewerAttributeValue(
            'webar/ar_button_settings/button_type'
        );
        $configButtonText = $this->getModelViewerAttributeValue(
            'webar/ar_button_settings/button_text'
        ) ?? "";

        if ($buttonType == 'custom_button') {
            $showCustomButton = 1;
        }
        
        if (!empty($configButtonText)) {
            $buttonText = __($configButtonText);
        }

        $btnConfig = [
            "buttonType" => $buttonType,
            "configButtonText" => $buttonText,
            "showCustomButton" => $showCustomButton
        ];
        return $btnConfig;
    }

    /**
     * Get flag value for Can Show Custom Image In Thumbnail
     *
     * @param string $modelThumbnail
     * @return int
     */
    public function canShowCustomImageInThumbnail($modelThumbnail = "")
    {
        $showCustomImageInThumbnail = 0;

        $thumbnailType = $this->getModelViewerAttributeValue(
            'webar/general_settings/model_thumbnail'
        ) ?? '';
        
        if ($thumbnailType == "custom_thumbnail_image" && $modelThumbnail != '') {
            $showCustomImageInThumbnail = 1;
        }
        return $showCustomImageInThumbnail;
    }

    /**
     * Get is product has place holder image
     *
     * @param int $imageCount
     * @param array $imgJsonArray
     * @return bool
     */
    public function getIsPlaceHolderImg($imageCount = 0, $imgJsonArray = [])
    {
        $isPlaceHolderImg = false;
        try {
            if ($imageCount == 1) {
                $imgUrl = $imgJsonArray[0]['img'] ?? "";
                $placeholderDir = 'images/product/placeholder/';
                if ($placeholderDir && str_contains($imgUrl, $placeholderDir)) {
                    $isPlaceHolderImg = true;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "getIsPlaceHolderImg method error:".$e->getMessage()
            );
        }
        return $isPlaceHolderImg;
    }

    /**
     * Get Main Image Data
     *
     * @return string
     */
    public function getMainImageData()
    {
        $mainImageData = "";
        try {
            $viewModel = $this;
            $images = $this->getGalleryImages()->getItems();
            $mainImage = current(array_filter($images, function ($img) use ($viewModel) {
                return $viewModel->isMainImage($img);
            }));

            if (!empty($images) && empty($mainImage)) {
                $mainImage = $this->getGalleryImages()->getFirstItem();
            }

            $helper = $this->getProductImageHelper();
            $mainImageData = $mainImage ?
                $mainImage->getData('medium_image_url') :
                $helper->getDefaultPlaceholderUrl('image');
        } catch (\Exception $e) {
            $this->logger->error(
                "getMainImageData method error:".$e->getMessage()
            );
        }
        return $mainImageData;
    }
}
