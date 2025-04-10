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

namespace Webkul\MobikulCore\Model\Notification\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    /**
     * MobikulNotification variable
     *
     * @var \Webkul\MobikulCore\Model\Notification
     */
    protected $mobikulNotification;

    /**
     * Construct function
     *
     * @param \Webkul\MobikulCore\Model\Notification $mobikulNotification
     */
    public function __construct(
        \Webkul\MobikulCore\Model\Notification $mobikulNotification
    ) {
        $this->mobikulNotification = $mobikulNotification;
    }

    /**
     * ToOptionArray function
     *
     * @return void
     */
    public function toOptionArray()
    {
        $availableOptions = $this->mobikulNotification->getAvailableTypes();
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
