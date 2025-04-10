<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\Walletsystem\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order;

/**
 * ViewTransaction resolver, used for GraphQL request processing.
 */
class ViewTransaction implements ResolverInterface
{
    
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory
     */
    private $wallettransactionModel;

    /**
     * @var WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var Order
     */
    private $order;

    /**
     * Constructor function
     *
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel
     * @param WallettransactionFactory $walletTransaction
     * @param Order $order
     */
    public function __construct(
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel,
        WallettransactionFactory $walletTransaction,
        Order $order
    ) {
        $this->wallettransactionModel = $wallettransactionModel;
        $this->walletTransaction = $walletTransaction;
        $this->order = $order;
    }

    /**
     * Resolver method for GraphQL
     *
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $ViewTransactionDetails=[];
            $trans_status = '';
            $walletTransaction = $this->wallettransactionModel->create()
                ->addFieldToFilter('customer_id', $args['customerId'])
                ->addFieldToFilter('entity_id', $args['entityId']);
            foreach ($walletTransaction as $record) {

                if ($record->getOrderId() != "0") {
                    $orderReference = $this->order->load($record->getOrderId());
                    $paymentTitle = $orderReference->getPayment()->getMethodInstance()->getTitle();
                    $_incrementId = $orderReference->getIncrementId();
                } else {
                    $_incrementId = "Not Available";
                    $paymentTitle = "Admin Adjustment";
                }
                
                if ($record->getStatus() == 1) {
                    $trans_status = 'Approved';
                } elseif ($record->getStatus() == 0) {
                    $trans_status = 'Pending';
                } else {
                    $trans_status = 'Cancel';
                }
                 $ViewTransactionDetails['Amount'] = $record->getAmount();
                 $ViewTransactionDetails['Action'] = $record->getAction();
                 $ViewTransactionDetails['Reference'] = $_incrementId;
                 $ViewTransactionDetails['Transaction_At'] = $record->getTransactionAt();
                 $ViewTransactionDetails['Transaction_note'] = $record->getTransactionNote();
                 $ViewTransactionDetails['Transaction_Status'] = $trans_status;
                 $ViewTransactionDetails['Payment_Method'] = $paymentTitle;
            }
                return $ViewTransactionDetails;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $lastTransactionDetails;
    }
}
