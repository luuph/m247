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
namespace Bss\RewardPoint\Controller\Notify;

use Magento\Framework\App\Action\Context;

/**
 * Class index
 *
 * Bss\RewardPoint\Controller\Notify
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\RewardPoint\Model\NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Bss\RewardPoint\Model\NotificationFactory $notificationFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Bss\RewardPoint\Model\NotificationFactory $notificationFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $notifyBalance = $this->getRequest()->getParam('notify_balance') ? 1 : 0;
        $notify_expiration = $this->getRequest()->getParam('notify_expiration') ? 1 : 0;
        $customerId = $this->getRequest()->getParam('customer_id');
        $model = $this->notificationFactory->create();
        $model->load($customerId);

        $dataNotify = [
            'customer_id' => $customerId,
            'notify_balance' => $notifyBalance,
            'notify_expiration' => $notify_expiration
        ];
        $model->addData($dataNotify);

        try {
            $model->save();
            $message = __('Save success');
            $status = 'success';
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $message = $e->getMessage();
            $status = 'error';
        } catch (\Exception $e) {
            $message = __('Something went wrong save.');
            $status = 'error';
        }

        return $resultJson->setData([
            'message' => $message,
            'status' => $status
        ]);
    }
}
