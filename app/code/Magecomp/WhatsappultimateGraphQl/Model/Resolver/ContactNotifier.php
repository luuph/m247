<?php
namespace Magecomp\Whatsappultimategraphql\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsappultimate\Api\UltimateInterface;

class ContactNotifier implements ResolverInterface
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
            if (empty($args['name']) || empty($args['email']) || empty($args['mobilenumber']) || empty($args['countrycode']) || empty($args['comment']) || empty($args['storeid'])) {
                $data = ["success" => false, "message" => __('Invalid parameter list.')];
                return $data;
            }
            $response=$this->ultimateInterface->sendContactNotification($args['name'],$args['email'],$args['mobilenumber'],$args['comment'],$args['countrycode'],$args['storeid']);
            $data = ["success" => true, "message" => $response];
            return $data;
        } catch (\Exception $exception) {
            $data = ["success" => false, "message" => __('Invalid data.')];
            return $data;
        }
    }
}