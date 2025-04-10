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

namespace Mageplaza\Affiliate\Block\Account;

use Mageplaza\Affiliate\Block\Account;

/**
 * Class Setting
 * @package Mageplaza\Affiliate\Block\Account
 */
class Campaigns extends Account
{
    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Affiliate Campaigns'));

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getSavePrefixUrl()
    {
        return $this->getUrl('*/account/saveCouponPrefix');
    }
    /**
     * @return string
     */
    public function getSharingUrl()
    {
        return $this->_affiliateHelper->getSharingUrl();
    }
}
