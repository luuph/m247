<?php
namespace Unveels\AddRoutePages\Model;

use Webkul\MobikulCore\Model\Bannerimage as BaseBannerimage;

class Bannerimage extends BaseBannerimage
{
    /**
     * Adding the new constant for "Page"
     */
    protected const TYPE_PAGE = "page";

    /**
     * Override getAvailableTypes function to add the "Page" option
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        $types = parent::getAvailableTypes();
        $types[self::TYPE_PAGE] = __("Page");

        return $types;
    }
}
