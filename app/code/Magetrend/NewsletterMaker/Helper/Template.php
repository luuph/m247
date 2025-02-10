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

namespace Magetrend\NewsletterMaker\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function replaceImageUrl($template, $content, $variables = [])
    {
        if (strpos($content, 'src="newsletter/') === false) {
            return $content;
        }

        if (isset($variables['subscriber'])) {
            $storeId = $variables['subscriber']->getStoreId();
        } else {
            $storeId = $template->getDesignConfig()->getStore();
        }

        $url = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $content = str_replace('src="newsletter/', 'src="'.$url.'newsletter/', $content);
        return $content;
    }

    public function cleanTemplate($templateHtml)
    {
        $templateHtml = str_replace([
            'data-css="true"',
            'contenteditable="true"'
        ], '', $templateHtml);

        $templateHtml = $this->removeAttribute($templateHtml, 'data-repeatable');
        $templateHtml = $this->removeAttribute($templateHtml, 'data-bgcolor');
        $templateHtml = $this->removeAttribute($templateHtml, 'data-color');

        return $templateHtml;
    }

    public function removeAttribute($html, $attribute)
    {
        if (strpos($html, $attribute) === false) {
            return $html;
        }

        $html = explode(' '.$attribute.'="', $html);
        foreach ($html as $key => $part) {
            if ($key == 0) {
                continue;
            }

            $part = explode('"', $part);
            unset($part[0]);
            $html[$key] = implode('"', $part);
        }

        $html = implode("", $html);
        return $html;
    }
}
