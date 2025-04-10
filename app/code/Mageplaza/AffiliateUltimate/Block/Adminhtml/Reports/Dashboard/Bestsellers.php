<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard;

use Magento\Framework\Phrase;
use Zend_Db_Expr;

/**
 * Class AffiliateUltimate
 * @package Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard
 */
class Bestsellers extends Reports
{
    const MAGE_REPORT_CLASS = Bestsellers::class;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AffiliateUltimate::reports/dashboard/bestsellers.phtml';

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Bestsellers Affiliate');
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        $store = $this->getRequest()->getParam('store');
        $dateRange = $this->getReportsHelper()->getDateRange();

        return $this->getUrl('affiliate/reports/bestsellers', [
            'startDate' => $dateRange[0],
            'endDate' => $dateRange[1],
            'store' => $store
        ]);
    }

    /**
     * @return mixed
     */
    public function getAffBestSellers()
    {
        $date = $this->getReportsHelper()->getDateRange();
        $itemCollection = $this->itemFactory->create()->addFieldToFilter('affiliate_commission', ['neq' => 'NULL']);
        $itemCollection->addFieldToFilter('main_table.created_at', ['gteq' => $date[0]])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $date[1]]);
        $store = $this->getRequest()->getParam('store');
        if ($store) {
            $itemCollection->addFieldToFilter('main_table.store_id', $store);
        }
        $itemCollection->getSelect()
            ->columns(['total_qty' => new Zend_Db_Expr('SUM(qty_ordered)')])
            ->group('sku')
            ->order('total_qty DESC');
        $itemCollection->setPageSize(5);

        return $itemCollection;
    }
}
