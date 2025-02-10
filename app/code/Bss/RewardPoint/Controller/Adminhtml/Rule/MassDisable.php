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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Mass Disable
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rule
 */
class MassDisable extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDisable constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\RewardPoint\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\RewardPoint\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('rule_id', ['in' => [$ids]]);

        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $this->saveIsActive($item);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been disabled.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save is active
     *
     * @param \Bss\RewardPoint\Model\Rule $item
     */
    private function saveIsActive($item)
    {
        try {
            $item->setIsActive(0);
            $item->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the earning rule.'));
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
