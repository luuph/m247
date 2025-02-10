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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class save
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rule
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\RewardPoint\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\RewardPoint\Model\RuleFactory $ruleFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\RewardPoint\Model\RuleFactory $ruleFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
        $this->backendSession = $backendSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->logger = $logger;
    }

    /**
     * Promo quote save action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var $model \Magento\SalesRule\Model\Rule */
        $model = $this->ruleFactory->create();
        if ($data) {
            try {
                $data = $this->getRequest()->getPostValue();

                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('The wrong earning rule is specified.'));
                    }
                }

                $validateResult = $model->validateData($this->dataObjectFactory->create()->addData($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->backendSession->setPageData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), 'type' => $data['type_rule']]);
                    return;
                }

                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);
                $model->setType($data['type_rule']);
                $this->backendSession->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the earning rule.'));
                $this->backendSession->setPageData(false);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the earning rule data. Please review the error log.')
                );
                $this->backendSession->setPageData($data);
                $this->logger->critical($e);
            }

            return $this->_getBackResultRedirect($resultRedirect, $model->getId());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get back result redirect after add/edit.
     *
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @param string $paramCrudId
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _getBackResultRedirect(
        \Magento\Framework\Controller\Result\Redirect $resultRedirect,
        $paramCrudId = null
    ) {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'id' => $paramCrudId,
                        '_current' => true,
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new', ['_current' => true]);
                break;
            default:
                $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
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
