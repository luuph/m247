<?php

namespace Unveels\UpdateMobileApi\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Response;
use Unveels\UpdateMobileApi\Api\CustomerTokenRepositoryInterface;
use Webkul\MobikulCore\Helper\Data;

class CustomerTokenRepository implements CustomerTokenRepositoryInterface
{
    private $customerRepository;
    private $helper;
    private $serializer;
    private $response;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Data $helper,
        SerializerInterface $serializer,
        Response $response
    ) {
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->serializer = $serializer;
        $this->response = $response;
    }

    public function updateCustomerMobile($customerToken, $newMobile)
    {
        try {
            if (empty($customerToken) || empty($newMobile)) {
                return $this->jsonResponse([
                    'response' => false,
                    'message'  => 'Customer token and mobile number are required.'
                ]);
            }

            $customerId = $this->helper->getCustomerByToken($customerToken);
            if (!$customerId) {
                return $this->jsonResponse([
                    'response' => false,
                    'message'  => 'Invalid customer token.'
                ]);
            }

            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('mobilenumber', $newMobile);
            $this->customerRepository->save($customer);

            return $this->jsonResponse([
                'response' => true,
                'message'  => 'Mobile number updated successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'response' => false,
                'message'  => $e->getMessage()
            ]);
        }
    }

    public function jsonResponse(array $data)
    {
        $serializedData = $this->serializer->serialize($data);

        return $this->response->setHeader('Content-Type', 'application/json', true)
            ->setBody($serializedData)
            ->sendResponse();
    }
}
