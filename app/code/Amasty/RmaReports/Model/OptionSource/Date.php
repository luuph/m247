<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class Date implements OptionSourceInterface
{
    public const WEEK = 1;
    public const MONTH = 2;
    public const YEAR = 3;
    public const CUSTOM = 4;

    public const DAY_END_POSTFIX = ' 23:59:59';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::WEEK,
                'label' => __('7 Days')
            ],
            [
                'value' => self::MONTH,
                'label' => __('30 Days')
            ],
            [
                'value' => self::YEAR,
                'label' => __('Last Year')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom Date')
            ]
        ];
    }
}
