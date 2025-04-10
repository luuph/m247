<?php
namespace Magecomp\WhatsappultimateGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;


class CreateCustomer implements ResolverInterface
{
    protected $storeManager;
    protected $customerFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['email']) || empty($args['password']) || empty($args['firstname']) || empty($args['lastname']) || empty($args['storeid'])) {
            $response = ['success' => false,'message' => __('Invalid parameter list.')];
            return $response;
        }  
        try{
            $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            if (!preg_match($regex, $args['email'])) {
                $response = ["status" => false, "errormessage" => __("Please enter proper email.")];
                return json_encode($response);
            }
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($args['storeid']);
            $customer->setEmail($args['email']); 
            $customer->setFirstname($args['firstname']);
            $customer->setLastname($args['lastname']);
            $customer->setPassword($args['password']);
            $customer->save();
            $customer->sendNewAccountEmail();
            $response = ['success' => true,'message' => __('Create customer sucessfully')];
            return $response;
        }
        catch (\Exception $e) {
            $response = ['success' => false,'message' => $e->getMessage()];
            return $response;
        } 
    }
}
