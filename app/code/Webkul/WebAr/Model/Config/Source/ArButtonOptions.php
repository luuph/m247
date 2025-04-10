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
 
class ArButtonOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get AR Button Options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [
            ['value' => 'default_button', 'label' => __('Default AR Button')],
            ['value' => 'custom_button', 'label' => __('Custom AR Button')]
        ];
        return $data;
    }
}
