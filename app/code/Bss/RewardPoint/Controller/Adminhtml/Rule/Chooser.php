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
namespace Bss\RewardPoint\Controller\Adminhtml\Rule;

/**
 * Class chooser
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rule
 */
class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        if ($request->getParam('email')) {
            $block = $this->_view->getLayout()->createBlock(
                \Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab\Conditions\Chooser\Customer::class,
                'rewardpoints_widget_chooser_email',
                ['data' => ['js_form_object' => $request->getParam('form')]]
            );
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }

    /**
     * Is allow
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::rule');
    }
}
