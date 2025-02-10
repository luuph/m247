<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param AutomationRuleRepositoryInterface $repository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        AutomationRuleRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * Delete action
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam(AutomationRuleInterface::RULE_ID)) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The rule has been deleted.'));

                return $this->resultRedirectFactory->create()->setPath('amrmaaut/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }

            return $this->resultRedirectFactory->create()->setPath(
                'amrmaaut/*/edit',
                [AutomationRuleInterface::RULE_ID => $id]
            );
        } else {
            $this->messageManager->addErrorMessage(__('Can\'t find a rule to delete.'));
        }

        return $this->resultRedirectFactory->create()->setPath('amrmaaut/*/');
    }
}
