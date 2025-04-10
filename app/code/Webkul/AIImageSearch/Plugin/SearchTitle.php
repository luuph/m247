<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIImageSearch
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AIImageSearch\Plugin;

class SearchTitle
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        private \Magento\Framework\Session\SessionManagerInterface $session
    ) {
    }

    /**
     * After Get Search Query Text
     *
     * @param \Magento\CatalogSearch\Block\Result $subject
     * @param string $result
     * @return string
     */
    public function afterGetSearchQueryText(\Magento\CatalogSearch\Block\Result $subject, $result)
    {
        $this->session->start();
        $imageSearch = $this->session->getWkAIImageSearch();
        $croppedImagePath = $this->session->getWkAIImageSearchCroppedImage();
        if ($imageSearch && !empty($croppedImagePath)) {
            $result = __("Image Search Result");
        }
        return $result;
    }
}
