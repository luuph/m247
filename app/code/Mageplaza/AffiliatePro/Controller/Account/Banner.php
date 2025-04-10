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
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Controller\Account;

use Magento\Framework\View\Result\Page;
use Mageplaza\Affiliate\Controller\Account;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Controller\Account
 */
class Banner extends Account
{
    /**
     * @return Page|void
     */
    public function execute()
    {
        if (!$this->dataHelper->isEnableReferFriend()) {
            $this->_forward('noroute');
        } else {
            return parent::execute();
        }
    }
}
