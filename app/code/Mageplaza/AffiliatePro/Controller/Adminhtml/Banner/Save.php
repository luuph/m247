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

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AffiliatePro\Controller\Adminhtml\Banner;

/**
 * Class Save
 * @package Mageplaza\AffiliatePro\Controller\Adminhtml\Banner
 */
class Save extends Banner
{
    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPost('banner');
        if (!$data) {
            return $resultRedirect->setPath('affiliate/*/');
        }

        /** @var \Mageplaza\Affiliate\Model\Banner $banner */
        $banner = $this->_initBanner();
        $bannerId = $this->getRequest()->getParam('id');

        if (!$banner->getId() && $bannerId) {
            $this->messageManager->addErrorMessage(__('This banner does not exist.'));

            return $resultRedirect->setPath('affiliate/*/');
        }

        if (!empty($data)) {
            $banner->addData($data);
            $this->_getSession()->setData('affiliate_banner_data', $data);
        }

        try {
            $banner->save();
            $this->_getSession()->setData('affiliate_banner_data', false);

            $this->messageManager->addSuccessMessage(__('You saved the banner.'));
        } catch (LocalizedException $e) {
            $redirectBack = true;
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $redirectBack = true;
            $this->messageManager->addErrorMessage(__('We cannot save the banner.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return ($redirectBack)
            ? $resultRedirect->setPath('affiliate/*/edit', ['id' => $banner->getId()])
            : $resultRedirect->setPath('affiliate/*/');
    }
}
