<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magezon\LookBook\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Payment\Model\Config\Source\Allmethods
     */
    protected $allPaymentMethods;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * @var  \Magezon\Core\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Payment\Model\Config\Source\Allmethods $allPaymentMethods
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magezon\Core\Framework\Serialize\Serializer\Json $serializer
     * @param \Magezon\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Payment\Model\Config\Source\Allmethods $allPaymentMethods,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magezon\Core\Framework\Serialize\Serializer\Json $serializer,
        \Magezon\Core\Helper\Data $coreHelper
    ) {
        parent::__construct($context);
        $this->storeManager                 = $storeManager;
        $this->urlBuilder                   = $urlBuilder;  
        $this->assetRepo                    = $assetRepo;  
        $this->allPaymentMethods            = $allPaymentMethods;  
        $this->pricingHelper                = $pricingHelper;  
        $this->dir                          = $dir;    
        $this->serializer                   = $serializer;
        $this->coreHelper                   = $coreHelper;  
    }
   
    /**
     * @param  string $key
     * @param  null|int $store
     * @return null|string
     */
    public function getConfig($key, $store = null)
    {
        $store     = $this->storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        $result    = $this->scopeConfig->getValue(
            'mgzlookbook/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $result;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getConfig('general/enabled');
    }

    public function getMainColor()
    {
        return $this->getConfig('general/main_color');
    }
    
    /**
     * @return string
     */
    public function getLookBookTitle()
    {
        return $this->getConfig('latest_page/title');
    }

    /**
     * @return string
     */
    public function getLookBookHomeLayout()
    {
        return $this->getConfig('latest_page/page_layout');
    }

    /**
     * @return string
     */
    public function getLookBookHomeLayoutType()
    {
        return $this->getConfig('latest_page/layout_type');
    }

    /**
     * @return number
     */
    public function getHomeNumberColumn() {
        return $this->getConfig('latest_page/number_column');
    }

    /**
     * @return number
     */
    public function getHomeProfilesPerPage()
    {
        return $this->getConfig('latest_page/number_profile_per_page');
    }

    /**
     * @return string
     */
    public function getProfileUrlSuffix() 
    {
        return $this->getConfig('permalink/profile_suffix');
    }

     /**
     * @return string
     */
    public function getRoute()
    {
        return $this->getConfig('permalink/route');
    }

    /**
     * @return string
     */
    public function getProfileUseCategories()
    {
        return $this->getConfig('permalink/profile_use_categories');
    }
    
    /**
     * @return string
     */
    public function getCategoryRoute()
    {
        return $this->getConfig('permalink/category_route');
    }

    /**
     * @return string
     */
    public function getCategoryUrlSuffix()
    {
        return $this->getConfig('permalink/category_suffix');
    }
   

    /**
     * @return string
     */
    public function getLookBookUrl()
    {
        return $this->urlBuilder->getUrl(null, ['_direct' => $this->getRoute()]);
    }

    /** 
     * @return string
     */
    public function getMarkerBackgroundColor()
    {
        return $this->getConfig('marker/marker_bg_color') ?: '#ffffff';
    }

    /** 
     * @return string
     */ 
    public function getMarkerTextColor()
    {
        return $this->getConfig('marker/marker_text_color') ?: '#333';
    }

    /** 
     * @return string
     */
    public function getMarkerBorder()
    {
        return $this->getConfig('marker/border');
    }

    /** 
     * @return string
     */
    public function getMarkerBorderColor()
    {
        return $this->getConfig('marker/border_color') ?: '#ddd';
    }

    /** 
     * @return number
     */
    public function getMarkerWidth()
    {
        return $this->getConfig('marker/width') ?: 40;
    }

    /** 
     * @return number
     */
    public function getMarkerHeight()
    {
        return $this->getConfig('marker/height') ?: 40;
    }

    /** 
     * @return string
     */
    public function getMarkerType()
    {
        return $this->getConfig('marker/marker_type');
    }

    /** 
     * @return string
     */
    public function getMarkerIcon($storeId = null)
    {
        return $this->getConfig('marker/marker_icon', $storeId);
    }

    /** 
     * @return string
     */
    public function getMarkerImage()
    {

        return $this->coreHelper->getMediaUrl() . 'banner/' . $this->getConfig('marker/marker_image');
    }


    /** 
     * @return string
     */
    public function getButtonAddAll()
    {
        return $this->getConfig('lookbook_page/enabled_btn');
    }

    /** 
     * @return string
     */
    public function getButtonTitle()
    {
        return $this->getConfig('lookbook_page/btn_title') ?:  __('Add All Products');
    }

    /**
     * @return string
     */
    public function getCategoryPageLayout()
    {
        return $this->getConfig('lookbook_cat_page/page_layout');
    }


    /**
     * @return string
     */
    public function getCategoryLayoutType()
    {
        return $this->getConfig('lookbook_cat_page/layout_type');
    }

    /**
     * @return number
     */
    public function getCategoryNumberColumn() {
        return $this->getConfig('lookbook_cat_page/number_column');
    }

    /**
     * @return number
     */
    public function getCategoryProfilesPerPage()
    {
        return $this->getConfig('lookbook_cat_page/number_profile_per_page');
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselDesktop()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/desktop') ?: 1;
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselTabletL()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/tablet_l') ?: 1;
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselTabletP()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/tablet_p') ?: 1;
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselMobieL()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/mobie_l') ?: 1;
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselMobieP()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/mobie_p') ?: 1;
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselNumberProfile()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/number_of_profile') ?: 3;
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselNavButton()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/nav_btn') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselDotNav()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/dot_nav') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselAutoHeight()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/auto_height') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselLoop()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/loop') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselAutoPlay()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/auto_play') ? 'true' : 'false';
    }

    /**
     * @return number
     */
    public function getCategoryPageCarouselAutoPlayTimeout()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/auto_play_timeout');
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselPauseOnMouseHover()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/pause_on_mouse_hover') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getCategoryPageCarouselRightToLeft()
    {
        return $this->getConfig('lookbook_cat_page/carousel_config/right_to_left') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getShowSidebarCat()
    {
        return $this->getConfig('sidebar/sidebar_cat/show_cat');
    }

    /**
     * @return string
     */
    public function getSidebarCategorySort()
    {
        return $this->getConfig('sidebar/sidebar_cat/sort_cat');
    }

    /**
     * @return string
     */
    public function getSidebarCategoryTitle()
    {
        $title = $this->getConfig('sidebar/sidebar_cat/title');
        if (!$title) {
            $title = __('Categories');
        }
        return $title;
    }

    /**
     * @return number|null
     */
    public function getSidebarNumberOfCategory()
    {
        $number = $this->getConfig('sidebar/sidebar_cat/number_of_cat');
        if (!$number) {
            $number = 5;
        }
        return $number;
    }

    /**
     * @return boolean
     */
    public function getShowSidebarProfile()
    {
        return $this->getConfig('sidebar/sidebar_profile/show_profile');
    }

    /**
     * @return string
     */
    public function getSidebarProfileSort()
    {
        return $this->getConfig('sidebar/sidebar_profile/sort_profile');
    }

    /**
     * @return string
     */
    public function getSidebarProfileTitle()
    {
        return $this->getConfig('sidebar/sidebar_profile/title') ?: __('Profiles');
    }

    /**
     * @return number|null
     */
    public function getSidebarNumberOfProfile()
    {
        return $this->getConfig('sidebar/sidebar_profile/number_of_profile') ?: 5;
    }

    /**
     * @return boolean
     */
    public function getProductPageEnabled()
    {
        return $this->getConfig('profile_product/enabled');
    }

    /**
     * @return string
     */
    public function getProductPageTitle()
    {
        return $this->getConfig('profile_product/title') ?: __('Product Profile');
    }

    /**
     * @return string
     */
    public function getProductPageProfileTitleColor()
    {
        return $this->getConfig('profile_product/profile_config/title_color');
    }

    /**
     * @return boolean
     */
    public function getProductPageProfileButtonEnabled()
    {
        return $this->getConfig('profile_product/profile_config/btn_link');
    }

    /**
     * @return string
     */
    public function getProductPageProfileButtonColor()
    {
        return $this->getConfig('profile_product/profile_config/btn_color');
    }

    /**
     * @return number
     */
    public function getProductPageCarouselDesktop()
    {
        return $this->getConfig('profile_product/carousel_config/desktop') ?: 4;
    }

    /**
     * @return number
     */
    public function getProductPageCarouselTabletL()
    {
        return $this->getConfig('profile_product/carousel_config/tablet_l') ?: 4;
    }

    /**
     * @return number
     */
    public function getProductyPageCarouselTabletP()
    {
        return $this->getConfig('profile_product/carousel_config/tablet_p') ?: 3;
    }

    /**
     * @return number
     */
    public function getProductPageCarouselMobieL()
    {
        return $this->getConfig('profile_product/carousel_config/mobie_l') ?: 2;
    }

    /**
     * @return number
     */
    public function getProductPageCarouselMobieP()
    {
        return $this->getConfig('profile_product/carousel_config/mobie_p') ?: 2;
    }

    /**
     * @return number
     */
    public function getProductPageCarouselNumberProfile()
    {
        return $this->getConfig('profile_product/carousel_config/number_of_profile') ?: 8;
    }

    /**
     * @return number
     */
    public function getProductPageCarouselMargin()
    {
        return $this->getConfig('profile_product/carousel_config/margin') ?: 10;
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselNavButton()
    {
        return $this->getConfig('profile_product/carousel_config/nav_btn') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselDotNav()
    {
        return $this->getConfig('profile_product/carousel_config/dot_nav') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselAutoHeight()
    {
        return $this->getConfig('profile_product/carousel_config/auto_height') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselLoop()
    {
        return $this->getConfig('profile_product/carousel_config/loop') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselAutoPlay()
    {
        return $this->getConfig('profile_product/carousel_config/auto_play') ? 'true' : 'false';
    }

    /**
     * @return number
     */
    public function getProductPageCarouselAutoPlayTimeout()
    {
        return $this->getConfig('profile_product/carousel_config/auto_play_timeout');
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselPauseOnMouseHover()
    {
        return $this->getConfig('profile_product/carousel_config/pause_on_mouse_hover') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getProductPageCarouselRightToLeft()
    {
        return $this->getConfig('profile_product/carousel_config/right_to_left') ? 'true' : 'false';
    }

    /**
     * @return boolean
     */
    public function getNavigationMenu() {
        return $this->getConfig('top_navigation/enabled');
    }

    /**
     * @return string
     */
    public function getImageLoading() {
        return $this->getViewImageUrl('Magezon_LookBook::images/loader-1.gif');
    }

    /**
     * @return string
     */
    public function getImageProfileTitleButtonProductPage() {
        return $this->getViewImageUrl('Magezon_LookBook::images/arrow.svg');
    }

    /**
     * Retrieve url of a view image
     *
     * @param string $image
     * @param array $params
     * @return string
     */
    public function getViewImageUrl($image, $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($image, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {}
    }
}