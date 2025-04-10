<?php

namespace Unveels\UpdateMobileApi\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Webkul\MobikulCore\Helper\Data;

class UpdateCustomerMobile implements ResolverInterface
{
    private $customerRepository;
    private $customerCollectionFactory;
    private $helper;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->helper = $helper;
    }

    public function resolve($field, $context, $info, array $value = null, array $args = null)
    {
        if (!isset($args['customerToken']) || !isset($args['newMobile'])) {
            throw new GraphQlInputException(__('Customer token and mobile number are required.'));
        }

        try {
            $customerId = $this->helper->getCustomerByToken($args['customerToken']);
            if (!$customerId) {
                throw new GraphQlInputException(__('Invalid customer token.'));
            }

            $newMobile = $args['newMobile'];

            // Check if mobile number is already in use by another customer
            $customerCollection = $this->customerCollectionFactory->create();
            $customerCollection->addAttributeToFilter('mobilenumber', $newMobile);
            $customerCollection->addFieldToFilter('entity_id', ['neq' => $customerId]); // Exclude the current customer

            if ($customerCollection->getSize() > 0) {
                throw new GraphQlInputException(__('This mobile number is already in use.'));
            }

            // Update the mobile number for the current customer
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('mobilenumber', $newMobile);
            $this->customerRepository->save($customer);

            return [
                'response' => true,
                'message' => 'Mobile number updated successfully.'
            ];
        } catch (\Exception $e) {
            return [
                'response' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
