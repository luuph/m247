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

namespace Bss\RewardPoint\Block\Adminhtml\SpentReport;

class Filter extends \Bss\RewardPoint\Block\Adminhtml\EarnReport\Filter
{
    /**
     * Block template file name
     *
     * @var string
     */
    protected $_template = 'Bss_RewardPoint::spentreport/filter.phtml';

    /**
     * Get currency
     *
     * @return mixed
     */
    public function getCurrencies()
    {
        return $this->getCollection()->getCurrencies();
    }
}
