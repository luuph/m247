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

namespace Mageplaza\AffiliatePro\Controller\Adminhtml\Banner;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\AffiliatePro\Controller\Adminhtml\Banner;

/**
 * Class Edit
 * @package Mageplaza\AffiliatePro\Controller\Adminhtml\Banner
 */
class Edit extends Banner
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $banner = $this->_initBanner();

        if (!$banner->getId() && $bannerId) {
            $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
            $this->_redirect('affiliate/*/');

            return;
        }

        $data = $this->_getSession()->getData('affiliate_banner_data', true);
        if (!empty($data)) {
            $banner->addData($data);
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_AffiliatePro::banner');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Banners'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $banner->getId() ? $banner->getTitle() : __('New Banner')
        );

        $this->_addBreadcrumb(
            $bannerId ? __('Edit Banner') : __('New Banner'),
            $bannerId ? __('Edit Banner') : __('New Banner')
        );
        $this->_view->renderLayout();
    }
}
