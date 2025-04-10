<?php
namespace Magecomp\Whatsappultimategraphql\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsappultimate\Api\UltimateInterface;

class VerifyUpdateOtp implements ResolverInterface
{
    protected $ultimateInterface;

    public function __construct(
        UltimateInterface $ultimateInterface
    ) {
        $this->ultimateInterface = $ultimateInterface;
    }
    
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            if (
                empty($args['newmobilenumber']) ||
                empty($args['oldmobilenumber']) ||
                empty($args['countrycode']) ||
                empty($args['customer_id']) ||
                empty($args['otp']) ||
                empty($args['storeid'])
            ) {
                throw new GraphQlInputException(__('Invalid parameter list.'));
            }
            $response = $this->ultimateInterface->verifyMobileUpdateOtp(
                $args['newmobilenumber'],
                $args['oldmobilenumber'],
                $args['countrycode'],
                $args['customer_id'],
                $args['otp'],
                $args['storeid']
            );
            // Return the response directly.
            return $response;
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }
}
