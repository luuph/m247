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

namespace Webkul\MobikulCore\Model\Featuredcategories\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * MobikulFeaturedcategories variable
     *
     * @var \Webkul\MobikulCore\Model\Featuredcategories
     */
    protected $mobikulFeaturedcategories;

    /**
     * Construct function
     *
     * @param \Webkul\MobikulCore\Model\Featuredcategories $mobikulFeaturedcategories
     */
    public function __construct(
        \Webkul\MobikulCore\Model\Featuredcategories $mobikulFeaturedcategories
    ) {
        $this->mobikulFeaturedcategories = $mobikulFeaturedcategories;
    }

    /**
     * ToOptionArray function
     *
     * @return void
     */
    public function toOptionArray()
    {
        $availableOptions = $this->mobikulFeaturedcategories->getAvailableStatuses();
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
