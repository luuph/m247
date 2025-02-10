<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Model\Media;

/**
 * Media configuration class
 */
class Config
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Returns relative media path
     *
     * @return string
     */
    public function getBaseMediaPath($templateId)
    {
        return 'newsletter/'.$templateId;
    }

    /**
     * Returns full media url
     *
     * @param $templateId
     * @return string
     */
    public function getBaseMediaUrl($templateId)
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'newsletter/'.$templateId;
    }

    /**
     * Returns media file url
     *
     * @param string $file
     * @param $templateId
     * @return string
     */
    public function getMediaUrl($file, $templateId)
    {
        return $this->getBaseMediaUrl($templateId) . '/' . $this->prepareFile($file);
    }

    /**
     * Returns full media path
     *
     * @param int $templateId
     * @param string $file
     * @return string
     */
    public function getMediaPath($file, $templateId)
    {
        return $this->getBaseMediaPath($templateId) . '/' . $this->prepareFile($file);
    }

    /**
     * Prepate file path
     *
     * @param string $file
     * @return string
     */
    public function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
