<?php
namespace Webkul\MobikulCore\Block;

class LightAppDemo extends \Magento\Config\Block\System\Config\Form\Field
{
    public const APP_TEMPLATE = 'Webkul_MobikulCore::system/config/lightappdemo.phtml';

    /**
     * Construct function
     *
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MobikulCore\Helper\Data $helper,
        array $data = []
    ) {
        $this->_assetRepo = $assetRepo;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate(static::APP_TEMPLATE);

        return $this;
    }

    /**
     * Get Html Element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Decorate field row html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $this->_toHtml() . '</tr>';
    }

    /**
     * GetMobileUrl function
     *
     * @return string
     */
    public function getMobileUrl()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/general_Settings_Mobile.png");
    }

    /**
     * GetDefaultLogo function
     *
     * @return string
     */
    public function getDefaultLogo()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/webkul.png");
    }

    /**
     * GetDefaultThemeColor function
     *
     * @return string
     */
    public function getDefaultThemeColor()
    {
        return $this->helper->getConfigData('mobikul/light_mode_config/light_app_theme_color') ?? '#ff8a80';
    }

    /**
     * GetBarIcon function
     *
     * @return string
     */
    public function getBarIcon()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/menu.png");
    }

    /**
     * GetCartIcon function
     *
     * @return string
     */
    public function getCartIcon()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/cart.png");
    }

    /**
     * GetSearchIcon function
     *
     * @return string
     */
    public function getSearchIcon()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/search.png");
    }

    /**
     * GetProductImage function
     *
     * @return string
     */
    public function getProductImage()
    {
        return $this->_assetRepo->getUrl("Webkul_MobikulCore::images/product.jpg");
    }

    /**
     * GetDefaultThemeTextColor function
     *
     * @return string
     */
    public function getDefaultThemeTextColor()
    {
        return $this->helper->getConfigData('mobikul/light_mode_config/light_app_theme_text_color') ?? '#ffffff';
    }

    /**
     * GetDefaultButtonColor function
     *
     * @return string
     */
    public function getDefaultButtonColor()
    {
        return $this->helper->getConfigData(
            'mobikul/light_mode_config/light_app_button_color'
        ) ?? '#00000';
    }

    /**
     * GetDefaultButtonTextColor function
     *
     * @return string
     */
    public function getDefaultButtonTextColor()
    {
        return $this->helper->getConfigData(
            'mobikul/light_mode_config/light_button_text_color'
        ) ?? '#ffffff';
    }
}
