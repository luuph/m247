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
 
class EnvImageOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get Environment Image Attribute Options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [
            ['value' => 'neutral', 'label' => __('Neutral')],
            ['value' => 'legacy', 'label' => __('Legacy')],
            ['value' => 'envurl', 'label' => __('URL to a .hdr or .jpg file')]
        ];
        return $data;
    }
}
