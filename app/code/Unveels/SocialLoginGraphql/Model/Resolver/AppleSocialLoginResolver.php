<?php
namespace Unveels\SocialLoginGraphql\Model\Resolver;

use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ResourceConnection;

class AppleSocialLoginResolver implements ResolverInterface
{
    private $tokenModelFactory;
    protected $storeManager;
    protected $customerFactory;
    protected $customerDataFactory;
    protected $customerRepository;
    protected $customerExtensionFactory;
    protected $resourceConnection;

    public function __construct(
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        TokenModelFactory $tokenModelFactory,
        CustomerExtensionFactory $customerExtensionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    private function jwtDecode($token)
    {
        $splitToken = explode(".", $token);
        $payloadBase64 = $splitToken[1];
        $decodedPayload = json_decode(urldecode(base64_decode($payloadBase64)), true);
        return $decodedPayload;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $token = $args['token'] ?? null;
        $firstName = $args['firstName'] ?? null;
        $lastName = $args['lastName'] ?? null;

        if (empty($token)) {
            throw new GraphQlInputException(__('The token should be provided.'));
        }

        try {
            $decoded = $this->jwtDecode($token);

            $email = $decoded["email"] ?? null;
            if (!$email) {
                throw new GraphQlInputException(__('The email is missing in the decoded token.'));
            }

            $firstName = $firstName ?? ($decoded["given_name"] ?? explode("@", $email)[0]);
            $lastName = $lastName ?? ($decoded["family_name"] ?? "user");
            $avatar = "";

            return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }    

    private function createSocialLogin($firstName, $lastName, $email, $avatar)
    {
        try {
            $existingCustomer = $this->customerRepository->get($email);
            $customerId = $existingCustomer->getId();

            $bearerToken = $this->generateBearerToken($customerId);
            $customerToken = $this->generateAndSaveCustomerToken($customerId);

            return ['token' => $bearerToken, 'customerToken' => $customerToken];
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerDataFactory->create();
            $customer->setFirstname($firstName)
                     ->setLastname($lastName)
                     ->setEmail($email)
                     ->setCustomAttribute('customer_avatar', $avatar);

            try {
                $customer = $this->customerRepository->save($customer);
                $customerId = $customer->getId();

                $bearerToken = $this->generateBearerToken($customerId);
                $customerToken = $this->generateAndSaveCustomerToken($customerId);

                return ['token' => $bearerToken, 'customerToken' => $customerToken];
            } catch (\Exception $e) {
                throw new GraphQlInputException(__('Failed to create a customer: ' . $e->getMessage()));
            }
        }
    }

    private function generateBearerToken($customerId)
    {
        return $this->tokenModelFactory->create()->createCustomerToken($customerId)->getToken();
    }

    private function generateAndSaveCustomerToken($customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('mobikul_oauth_token');
    
        // Check for an existing token
        $select = $connection->select()
            ->from($tableName, ['token'])
            ->where('customer_id = ?', $customerId)
            ->limit(1);
    
        $existingToken = $connection->fetchOne($select);
    
        if ($existingToken) {
            // Return the existing token
            return $existingToken;
        }
    
        // Generate a new token if none exists
        $newToken = bin2hex(random_bytes(16));
        $data = [
            'customer_id' => $customerId,
            'token' => $newToken,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    
        $connection->insertOnDuplicate($tableName, $data);
    
        return $newToken;
    }
    
}
