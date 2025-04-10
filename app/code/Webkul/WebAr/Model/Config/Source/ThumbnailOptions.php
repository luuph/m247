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
namespace Webkul\WebAr\Model\Config\Source;
 
class ThumbnailOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get Thumbnail Options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [
            ['value' => 'main_image', 'label' => __('Product Main Image')],
            ['value' => 'custom_thumbnail_image', 'label' => __('3D Model Thumbnail Image')]
        ];
        return $data;
    }
}
