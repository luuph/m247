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
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Bss\CustomerApproval\Model\ResourceModel\Options;

class MassDisapproved extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Options
     */
    protected $optionModel;

    /**
     * MassDisapproved constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory $customerFactory
     * @param Options $optionModel
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerFactory $customerFactory,
        Options $optionModel
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerFactory = $customerFactory;
        $this->optionModel = $optionModel;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersUpdated = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getAllIds() as $customerId) {
                // Verify customer exists
            $customer = $this->getCustomer($customerId);
            $disapprove = (int) $this->optionModel->getStatusValue('Disapproved')['option_id'];
            $customerDataModel = $customer->getDataModel();
            $customerDataModel->setCustomAttribute("activasion_status", $disapprove);
            $customer->updateData($customerDataModel);
            $this->saveAttribute($customer);
            $customersUpdated++;
        }
        if ($customersUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $customersUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    protected function saveAttribute($customer)
    {
        return $customer->save();
    }

    protected function getCustomer($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }
}
