<?php

namespace Lof\CustomerAvatar\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class DeleteCustomerAvatar implements ResolverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * DeleteCustomerAvatar constructor.
     *
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Resolve the GraphQL mutation.
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (!$context->getUserId()) {
            throw new LocalizedException(__('You must be logged in to delete your avatar.'));
        }

        try {
            $customer = $this->customerSession->getCustomer();
            $avatarAttribute = $customer->getProfilePicture();

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DeleteAvatarGraphQl.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $logger->info('Deleting avatar for customer ID: ' . $customer->getId());
            $logger->info('Current avatar value: ' . $avatarAttribute);

            if ($avatarAttribute) {
                $customer->setProfilePicture("");
                $customer->save();

                $logger->info('Avatar deleted successfully.');

                return [
                    'success' => true,
                    'message' => __('Avatar deleted successfully.'),
                ];
            }

            return [
                'success' => false,
                'message' => __('No avatar found to delete.'),
            ];
        } catch (LocalizedException $e) {
            $logger->info('LocalizedException: ' . $e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $logger->info('General Exception: ' . $e->getMessage());
            throw new LocalizedException(__('Something went wrong while deleting the avatar.'));
        }
    }
}
