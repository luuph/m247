<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Plugin;

class AfterMoveImage
{
    /**
     * @param \Bss\CustomOptionImage\Helper\ImageSaving $subject
     * @param string $result
     * @param mixed $value
     * @return string
     */
    public function afterMoveImage(
        \Bss\CustomOptionImage\Helper\ImageSaving $subject,
        $result,
        $value
    ) {
        $img = $value->getData('image_url');
        if ($img && strpos($img, 'customoptiontemplate') !== false && $value->getData('bss_image_button') =='') {
            $baseMediaUrl = $subject->getMediaBaseUrl();
            $pathUrl = str_replace($baseMediaUrl, '', $img);
            return $pathUrl;
        }
        return $result;
    }

    /**
     * @param \Bss\CustomOptionImage\Helper\ImageSaving $subject
     * @param string $result
     * @param mixed $value
     * @return string
     */
    public function afterMoveImageSwatch(
        \Bss\CustomOptionImage\Helper\ImageSaving $subject,
        $result,
        $value
    ) {
        $img = $value->getData('swatch_image_url');
        if ($img && strpos($img, 'customoptiontemplate') !== false && $value->getData('swatch_image_url_hidden') =='') {
            $baseMediaUrl = $subject->getMediaBaseUrl();
            return str_replace($baseMediaUrl, '', $img);
        }
        return $result;
    }
}
