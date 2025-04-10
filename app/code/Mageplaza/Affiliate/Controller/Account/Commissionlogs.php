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

use Magento\Framework\View\Result\Page;
use Mageplaza\Affiliate\Controller\Account;
use Mageplaza\Affiliate\Helper\Data;

/**
 * Class History
 * @package Mageplaza\Affiliate\Controller\Account
 */
class Commissionlogs extends Account
{
    /**
     * @return Page
     */
    public function execute()
    {
        if($this->getRequest()->isAjax()) {
            $type = $this->getRequest()->getParam('type');
            if($type === 'withdraw') {
                $html = $this->_view->getLayout()
                    ->createBlock(\Mageplaza\Affiliate\Block\Account\Commissionlogs\Withdraw::class)
                    ->setTemplate("Mageplaza_Affiliate::account/commissionlogs/withdraw.phtml")
                    ->toHtml();

                return $this->getResponse()->representJson(Data::jsonEncode([
                    'html' =>   $html,
                    'type' => $type
                ]));
            } else {
                $html = $this->_view->getLayout()
                    ->createBlock(\Mageplaza\Affiliate\Block\Account\Commissionlogs\Commissions::class)
                    ->setTemplate("Mageplaza_Affiliate::account/commissionlogs/commissions.phtml")
                    ->toHtml();
                return $this->getResponse()->representJson(Data::jsonEncode([
                    'html' =>   $html,
                    'type' => $type
                ]));
            }

        } else {
            return parent::execute();
        }

    }
}
