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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Controller\Account;

use Mageplaza\Affiliate\Controller\Account;
use Mageplaza\Affiliate\Helper\Data;

/**
 * Class Withdraw
 * @package Mageplaza\Affiliate\Controller\Account
 */
class Withdraw extends Account
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if($this->getRequest()->isAjax()) {
            $html = $this->_view->getLayout()
                ->createBlock(\Mageplaza\Affiliate\Block\Account\Withdraw\Transaction::class)
                ->setTemplate("Mageplaza_Affiliate::account/withdraw/transactions.phtml")
                ->toHtml();
            return $this->getResponse()->representJson(Data::jsonEncode([
                'html' =>   $html
            ]));
        } else {
            return parent::execute();
        }

    }
}
