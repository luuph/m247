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
use Mageplaza\Affiliate\Helper\Data;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Controller\Account
 */
class getContentBanner extends Account
{

    /**
     * @return Page
     */
    public function execute()
    {

        if ($this->getRequest()->isAjax()) {
            $campaign_id = $this->getRequest()->getParam('campaign_id');

            $html = $this->_view->getLayout()
                ->createBlock(\Mageplaza\AffiliatePro\Block\Account\Banner::class);

            $html->setCampaignId($campaign_id);
            $htmlContent = $html->setTemplate("Mageplaza_AffiliatePro::account/banner/content_banner.phtml")
                ->toHtml();
//

            return $this->getResponse()->representJson(Data::jsonEncode([
                'html' => $htmlContent
            ]));

        } else {
            return parent::execute();
        }
    }
}
