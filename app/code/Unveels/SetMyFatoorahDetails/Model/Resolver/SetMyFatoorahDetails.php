<?php
namespace Unveels\SetMyFatoorahDetails\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SetMyFatoorahDetails implements ResolverInterface
{
    protected $resource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $invoiceId = $args['invoice_id'] ?? null;
        $orderIncrementId = $args['order_increment_id'] ?? null;
        $gatewayName = $args['gateway_name'] ?? null;

        $connection = $this->resource->getConnection();
        $tableName = $connection->getTableName('myfatoorah_invoice');

        if ($orderIncrementId) {
            $select = $connection->select()
                ->from($tableName)
                ->where('order_id = ?', $orderIncrementId);

            $row = $connection->fetchRow($select);

            $data = [
                'order_id' => $orderIncrementId,
                'customer_reference' => $orderIncrementId,
                'invoice_id' => $invoiceId,
                'gateway_name' => $gatewayName,
            ];

            if ($row) {
                $connection->update(
                    $tableName,
                    $data,
                    ['order_id = ?' => $orderIncrementId]
                );
            } else {
                $connection->insert($tableName, $data);
            }
        }

        return ['success' => true];
    }
}
