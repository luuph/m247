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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\Order\Grid;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class CollectionPlugin
{
    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    public function __construct(
        TimezoneInterface $timeZone
    ) {
        $this->timeZone = $timeZone;
    }

    /**
     * Set field main_table.created_at to filter
     *
     * @param Collection $subject
     * @param $field
     * @param null $condition
     * @return void
     * @throws LocalizedException
     */
    public function beforeAddFieldToFilter(
        Collection $subject,
                   $field,
                   $condition = null
    ) {
        if ($field === 'main_table.created_at') {
            if (is_array($condition)) {
                foreach ($condition as $key => $value) {
                    $condition[$key] = $this->timeZone->convertConfigTimeToUtc($value);
                }
            }
        }

        return [$field, $condition];
    }
}
