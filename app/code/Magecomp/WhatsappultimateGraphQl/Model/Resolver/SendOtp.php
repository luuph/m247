<?php
namespace Magecomp\Whatsappultimategraphql\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsappultimate\Api\UltimateInterface;

class SendOtp implements ResolverInterface
{
    protected $ultimateInterface;

    public function __construct(
        UltimateInterface $ultimateInterface
    ) {
        $this->ultimateInterface=$ultimateInterface;
    }
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            // Use isset() to allow false for isresend if needed.
            if (!isset($args['mobilenumber']) || !isset($args['countrycode']) || !isset($args['isresend']) || !isset($args['storeid'])) {
                throw new GraphQlInputException(__('Invalid parameter list.'));
            }
            // Call sendRegotp and return its result directly.
            $response = $this->ultimateInterface->sendRegotp(
                $args['mobilenumber'],
                $args['countrycode'],
                $args['isresend'],
                $args['storeid']
            );
            return $response;
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }
    
}