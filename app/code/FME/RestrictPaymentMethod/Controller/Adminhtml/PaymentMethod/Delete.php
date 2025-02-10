<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Controller\Adminhtml\PaymentMethod;

use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use FME\RestrictPaymentMethod\Model\PaymentMethodFactory;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Backend\App\Action
{

    public function __construct(
        Registry $coreRegistry,
        Context $context,
        PaymentMethodFactory $typeFactory,
        LoggerInterface $logger
    ) {

        $this->coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->typeFactory = $typeFactory;
        parent::__construct($context);
    }

    /**
     * Delete Payment Method Restriction Rule action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->typeFactory->create();/** @var \FME\RestrictPaymentMethod\Model\PaymentMethod $model */
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Payment Method Restriction Rule Successfully.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e . 'We can\'t delete the Payment Method Restriction Rule right now. Please review the log and try again.'));
                $this->logger->critical($e);
                $this->_redirect('paymentmethod/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Payment Method Restriction Rule to delete.'));
        $this->_redirect('*/*/index');
    }
        /**
         * Check Payment Method Restriction Rule recode delete Permission.
         * @return bool
         */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_RestrictPaymentMethod::paymentmethod');
    }
}
