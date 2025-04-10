<?php
namespace Unveels\SocialLoginGraphql\Model\Resolver;

use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ResourceConnection;

class SocialLoginResolver implements ResolverInterface
{
    protected $customerDataFactory;
    protected $customerRepository;
    private $tokenModelFactory;
    protected $resourceConnection;

    public function __construct(
        CustomerInterfaceFactory $customerDataFactory,
        CustomerRepositoryInterface $customerRepository,
        TokenModelFactory $tokenModelFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->tokenModelFactory = $tokenModelFactory;
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
        $token = $args['token'];
        $type = $args['type'];

        if (empty($token)) {
            throw new GraphQlInputException(__('The token should be provided.'));
        }

        try {
            switch ($type) {
                case 'facebook':
                    $fields = "id,name,first_name,last_name,email,picture.type(large)";
                    $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $token;
                    break;

                case 'google':
                    $url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $token;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    $result = json_decode($result, true);

                    if (!$result) {
                        throw new GraphQlInputException(__('Invalid response from Google API.'));
                    }

                    $firstName = $result["given_name"] ?? "Guest";
                    $lastName = $result["family_name"] ?? "User";
                    $email = $result["email"] ?? null;
                    $avatar = $result["picture"] ?? "";

                    if ($email) {
                        return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
                    } else {
                        throw new GraphQlInputException(__('Google API did not return an email address.'));
                    }

                case 'firebase_sms':
                    $firstName = $token;
                    $lastName = "unveels";
                    $email = $token . "@unveels.io";
                    $avatar = "";
                    return $this->createSocialLogin($firstName, $lastName, $email, $avatar);

                case 'apple':
                    $decoded = $this->jwtDecode($token);
                    $email = $decoded["email"];
                    $firstName = explode("@", $email)[0];
                    $lastName = "user";
                    $avatar = "";
                    return $this->createSocialLogin($firstName, $lastName, $email, $avatar);

                default:
                    throw new GraphQlInputException(__('Unsupported social login type.'));
            }
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
