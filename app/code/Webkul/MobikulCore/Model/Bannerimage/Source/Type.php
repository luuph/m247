<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Bannerimage\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type model
 */
class Type implements OptionSourceInterface
{
    /**
     * MobikulBannerimage variable
     *
     * @var \Webkul\MobikulCore\Model\Bannerimage
     */
    protected $mobikulBannerimage;

    /**
     * Construct function
     *
     * @param \Webkul\MobikulCore\Model\Bannerimage $mobikulBannerimage
     */
    public function __construct(
        \Webkul\MobikulCore\Model\Bannerimage $mobikulBannerimage
    ) {
        $this->mobikulBannerimage = $mobikulBannerimage;
    }

    /**
     * ToOptionArray function
     *
     * @return void
     */
    public function toOptionArray()
    {
        $availableOptions = $this->mobikulBannerimage->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                "label" => $value,
                "value" => $key
            ];
        }
        return $options;
    }
}
