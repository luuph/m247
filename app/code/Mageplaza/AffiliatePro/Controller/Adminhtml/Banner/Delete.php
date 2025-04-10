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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\AffiliatePro\Controller\Adminhtml\Banner;

/**
 * Class Delete
 * @package Mageplaza\AffiliatePro\Controller\Adminhtml\Banner
 */
class Delete extends Banner
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Mageplaza\AffiliatePro\Model\Banner $banner */
                $banner = $this->_objectManager->create('Mageplaza\AffiliatePro\Model\Banner');
                $banner->load($id)->delete();

                $this->messageManager->addSuccessMessage(__('The Banner has been deleted.'));
                $this->_redirect('affiliate/*/');

                return;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while deleting banner data. Please review the action log and try again.')
                );
                $this->_redirect('affiliate/*/edit', ['id' => $id]);

                return;
            }
        }

        $this->messageManager->addErrorMessage(__('We cannot find a banner to delete.'));
        $this->_redirect('affiliate/*/');
    }
}
