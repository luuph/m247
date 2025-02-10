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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Plugin\Block\Widget\Grid;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Model\CurrencyHeader;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class ColumnSet
{
    /**
     * @var CurrencyHeader
     */
    protected $currencyHeader;

    /**
     * @var bool
     */
    protected $addCurrencyToColumn = true;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param CurrencyHeader $currencyHeader
     * @param Data $helperData
     */
    public function __construct(
        \Bss\RewardPoint\Model\CurrencyHeader $currencyHeader,
        \Bss\RewardPoint\Helper\Data          $helperData
    ) {
        $this->currencyHeader = $currencyHeader;
        $this->helperData = $helperData;
    }

    /**
     * Calculate column discount/ order
     *
     * @param \Magento\Backend\Block\Widget\Grid\ColumnSet $subject
     * @param \Magento\Framework\DataObject $result
     * @return mixed
     */
    public function afterGetTotals(
        $subject,
        $result
    ) {
        if ($this->helperData->isActive()) {
            $data = $result->getData();
            if (is_array($data)
                && array_key_exists('spent_report_rate', $data)
                && array_key_exists('spent_report_discount', $data)
                && array_key_exists('spent_point_value_order', $data)
                && $result['spent_point_value_order'] != 0
            ) {
                $result['spent_report_rate'] = round(100 *
                    $result['spent_report_discount'] / $result['spent_point_value_order'], 2);
            }
        }
        return $result;
    }

    /**
     * Add currency to column discount
     *
     * @param \Magento\Backend\Block\Widget\Grid\ColumnSet $subject
     * @param DataObject $result
     * @return mixed
     * @throws CurrencyException
     * @throws LocalizedException
     */
    public function afterGetColumns(
        $subject,
        $result
    ) {
        if ($this->helperData->isActive()) {
            if ($this->addCurrencyToColumn && is_array($result) && array_key_exists('bss_spent_report_discount', $result) && array_key_exists('bss_spent_report_value_order', $result)) {
                $currency = $this->helperData->getCurrencyName($this->currencyHeader->currency);
                $result['bss_spent_report_discount']->setHeader($result['bss_spent_report_discount']['header'] .= " (" . $currency . ")");
                $result['bss_spent_report_value_order']->setHeader($result['bss_spent_report_value_order']['header'] .= " (" . $currency . ")");
                $this->addCurrencyToColumn = false;
            }
        }
        return $result;
    }
}
