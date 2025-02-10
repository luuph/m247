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
namespace Bss\RewardPoint\Controller\Adminhtml\Customer\Action\Rewardpoints;

use Bss\RewardPoint\Model\Config\Source\TransactionActions;

/**
 * Class save
 *
 * Bss\RewardPoint\Controller\Adminhtml\Customer\Action\Rewardpoints
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\Auth\Sessio
     */
    protected $authSession;

    /**
     * @var \ss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\MultipleTransaction
     */
    protected $multipleTransaction;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Model\ResourceModel\MultipleTransaction $multipleTransaction
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Model\ResourceModel\MultipleTransaction $multipleTransaction
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->authSession        = $authSession;
        $this->multipleTransaction = $multipleTransaction;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            $data['point'] = (int) $params['point'];
            if ($data['point'] !== 0) {
                $data['website_id'] = $params['website_id'];
                $data['note'] = $params['note'];
                $data['action'] = TransactionActions::ADMIN_CHANGE;
                $data['created_by'] = $this->getCurrentUser()->getEmail();
                $data['created_at'] = $this->helper->getCreateAt();
                $data['expires_at'] = $this->helper->getExpireDay($data['website_id']);
                $data['is_expired'] = (bool)$this->helper->getExpireDay($data['website_id']);

                $customer_ids = $this->getRequest()->getParam('customer_ids');

                try {
                    $this->multipleTransaction->insertMultiple($customer_ids, $data);
                    $this->messageManager->addSuccessMessage(__('Update reward points success.'));
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while updating reward points.')
                    );
                }
            }
        }
        return $resultRedirect->setPath('customer/index/index');
    }

    /**
     * Get current user
     *
     * @return \Magento\User\Model\User|null
     */
    private function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
}
