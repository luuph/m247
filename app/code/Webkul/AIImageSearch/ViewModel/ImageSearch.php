<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_AIImageSearch
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AIImageSearch\ViewModel;

use Magento\Framework\Session\SessionManagerInterface;

class ImageSearch implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Dependency Initilization
     *
     * @param SessionManagerInterface $session
     * @param \Webkul\AIImageSearch\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholder
     */
    public function __construct(
        protected SessionManagerInterface $session,
        private \Webkul\AIImageSearch\Helper\Data $helper,
        protected \Magento\Framework\App\Request\Http $request,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholder,
    ) {
    }

    /**
     * Get Session
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get Placeholder Image
     *
     * @return string
     */
    public function getPlaceholderImage()
    {
        return $this->placeholder->create(['type' => 'image'])->getUrl();
    }

    /**
     * Get Request Params
     *
     * @return array
     */
    public function getRequerstParams()
    {
        return $this->request->getParams();
    }

    /**
     * Get Media Directory Url
     *
     * @return string
     */
    public function getMediaDirectoryUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * Get Media Directory Url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::DEFAULT_URL_TYPE
        );
    }

    /**
     * Get Media Directory Url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl();
    }

    /**
     * Get file content in base64
     *
     * @param string $filePath
     * @return string
     */
    public function getFileInBase64Encode($filePath)
    {
        return $this->helper->getFileInBase64Encode($filePath);
    }
}
