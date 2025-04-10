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

namespace Webkul\MobikulCore\Model\Order\AttributeSet;

/**
 * Options Class for filrter Options.
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{

    protected const STATUS_IOS = "Ios";

    protected const STATUS_ANDROID = "Android";

    protected const STATUS_WEB = "Web";

    /**
     * @var null|array
     */
    protected $options;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_IOS => __('ios'),
            self::STATUS_ANDROID => __('android'),
            self::STATUS_WEB => __('web')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return array
     */
    public function getAllOptions()
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }
    
    /**
     * ToOptionArray option array with empty value
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = $this->getAllOptions();
        return $this->options;
    }
}
