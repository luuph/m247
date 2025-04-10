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
namespace Webkul\WebAr\Plugin\ConfigurableProduct\Block;

class ConfigurableAfterGetJsonConfigPlugin
{
    /**
     * @var \Webkul\WebAr\ViewModel\DataModel
     */
    protected $viewModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\WebAr\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\WebAr\Logger\Logger $logger
     * @param \Webkul\WebAr\ViewModel\DataModel $viewModel
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @return void
     */
    public function __construct(
        \Webkul\WebAr\Logger\Logger $logger,
        \Webkul\WebAr\ViewModel\DataModel $viewModel,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->logger         = $logger;
        $this->request        = $request;
        $this->viewModel      = $viewModel;
        $this->productFactory = $productFactory;
    }

    /**
     * Composes configuration for js
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $resultJson
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $resultJson
    ) {
        //Check if request is made from product page//
        if (strtolower($this->request->getFullActionName()) == "catalog_product_view") {
            $result = $this->viewModel->getJsonHelper()->jsonDecode($resultJson);

            /////Set WebAR Configuration/////
            $result["webArConfig"] = $this->getWebArConfigData($result["productId"] ?? 0);
            $resultJson = $this->viewModel->getJsonHelper()->jsonEncode($result);
            /////////
        }
       
        return $resultJson;
    }

    /**
     * Get Web AR Configuration Data
     *
     * @param int $productId
     * @return array
     */
    protected function getWebArConfigData($productId = 0)
    {
        if (!$productId) {
            return [];
        }

        $arConfig = [];
        $display = false;
        $modelUrl = $iosModelUrl = '';

        try {
            $product = $this->productFactory->create()->load($productId);

            $productType = $product->getTypeId();
            $moduleStatus = $this->viewModel->isModuleEnabled();

            //Get Media Url
            $mediaUrl = $this->viewModel->getMediaUrl();

            //Show Seperate GLB for Configurable Product
            $showSeperateGlb = (int)$product->getAllowGlbInChildProducts();
            $associatedProducts = ($productType == "configurable") ?
                $this->viewModel->getAssociatedProductModels($product) : [];
            //////////////

            ///Flag to show media gallery images
            $canShowOtherImages = $this->viewModel->canShowOtherImages();

            ////Get Button Configurations///
            $btnConfig = $this->viewModel->getARButtonConfig();

            ///Lighting And Skybox Attributes Configurations/////
            $lightAttrs = $this->viewModel->getLightingAndSkyboxAttributesConfig();

            ///Staging And Cameras Attributes Configurations/////
            $cameraAttrs = $this->viewModel->getStagingAndCamerasAttributesConfig();

            ///Image configurations///
            $imagesConfig = $this->viewModel->getImageAttributesConfig();

            ///Loading Attributes Configurations/////
            $loading = $this->viewModel->getModelViewerAttributeValue(
                'webar/loading_attribute_settings/loading'
            ) ?? 'auto';

            ////Flag to show custom image in AR Model thumbnail in media gallery
            $showCustomImageInThumbnail = $this->viewModel->canShowCustomImageInThumbnail(
                $imagesConfig["modelThumbnail"]
            );

            ///Get AR Model Attributes' Configuration Json
            $attrConfigJson = $this->viewModel->getAttributeConfigJson(
                $lightAttrs,
                $cameraAttrs,
                $imagesConfig,
                $loading
            );
            $attrConfigArray = $this->viewModel->getJsonHelper()->jsonDecode($attrConfigJson);
            //////

            //////Set Model Urls///////
            if ($moduleStatus) {
                $display = true;
                $check3dEnabled = $this->viewModel->get3dModelFile($product->getId());
                if (!empty($check3dEnabled['model_file'])) {
                    $modelUrl =  $mediaUrl.'catalog/product/glbmodels/'.$check3dEnabled['model_file'];
                }
                if (!empty($check3dEnabled['ios_file'])) {
                    $iosModelUrl =  $mediaUrl.'catalog/product/glbmodels/'.'ios/'.$check3dEnabled['ios_file'];
                }
            }
        
            ////Get Associated Product Thumbnails
            $associatedProductThumbs = $this->viewModel->getAssociatedProductThumbs() ?? [];
            //////

            if ($showSeperateGlb == 1 && $modelUrl != "") {
                $modelUrl = "";
            }

            ////If modelUrl is empty and showSeperateGlb is true, then set modelUrl////
            if ($modelUrl == "" && $showSeperateGlb == 1 && $display && !empty($associatedProducts)) {
                $modelUrl = $this->viewModel->getDefault3DModelUrl();
                $iosModelUrl = $this->viewModel->getDefault3DIosModelUrl();
            }
            //////

            /// Get Model Variant Attribute/////
            $defaultVariantAttribute = ($productType == "configurable") ?
                $product->getModelVariantAttribute() : '';
            if ($defaultVariantAttribute == "") {
                $defaultVariantAttribute = "0";
            }
            /////
            $firstAssociatedProduct = $this->viewModel->getFirstAssociatedProductId();
            
            $arConfig["mainImageData"] = $this->viewModel->getMainImageData(); //Get Main Image for media gallery
            $arConfig["showCustomImageInThumbnail"] = $showCustomImageInThumbnail;
            $arConfig["modelUrl"] =  $modelUrl;
            $arConfig["iosModelUrl"] =  $iosModelUrl;
            $arConfig["displayModel"] =  $display;
            $arConfig["attributeConfigs"] =  $attrConfigArray;
            $arConfig["productHasOptions"] = (int)$product->hasOptions() ?? 0;
            $arConfig["showCustomButton"] = (int)$btnConfig["showCustomButton"] ?? 0;
            $arConfig["showCustomButtonText"] = $btnConfig["configButtonText"];
            $arConfig["productType"] = $productType;
            $arConfig["canShowOtherImages"] = $canShowOtherImages;
            $arConfig["showSeperateGlb"] =  $showSeperateGlb;
            $arConfig["associatedProducts"] =  $associatedProducts;
            $arConfig["firstAssociatedProduct"] = $firstAssociatedProduct;
            $arConfig["associatedProductThumbs"] = $associatedProductThumbs;
            $arConfig["defaultVariantAttribute"] = $defaultVariantAttribute;

        } catch (\Exception $e) {
            $this->logger->error(
                "getWebArConfigData Error:".$e->getMessage()
            );
        }
        
        return $arConfig;
    }
}
