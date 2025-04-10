<?php

namespace Lof\CustomerAvatar\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class GetCustomerAvatar implements ResolverInterface
{
    private $customerRepository;

    public function __construct(
        CustomerRepository $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerId = $args['customer_id'];

        try {
            // Load the customer by ID
            $customer = $this->customerRepository->getById($customerId);

            // Get the profile picture attribute
            $profilePicture = $customer->getCustomAttribute('profile_picture');
            $avatarUrl = null;

            if ($profilePicture && $profilePicture->getValue()) {
                $avatarPath = 'media/customer/' . ltrim($profilePicture->getValue(), '/');
                $avatarUrl = $avatarPath;
            }

            return [
                'customer_id' => $customerId,
                'avatar_url' => $avatarUrl
            ];
        } catch (\Exception $e) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(__('Customer not found or invalid'));
        }
    }
}
