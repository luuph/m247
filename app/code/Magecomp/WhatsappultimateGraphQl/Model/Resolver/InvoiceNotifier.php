<?php
namespace Magecomp\Whatsappultimategraphql\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsappultimate\Api\UltimateInterface;

class InvoiceNotifier implements ResolverInterface
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
            if (empty($args['invoiceId']) || empty($args['isresend'])) {
                throw new GraphQlInputException(__('Invalid parameter list.'));
            }
            $response=$this->ultimateInterface->sendInvoiceNotification($args['invoiceId'],$args['isresend']);
            $data = ["success" => true, "message" => $response];
            return $data;
        } catch (\Exception $exception) {
            $output['status'] = $exception;
            return $output;
        }
    }
}