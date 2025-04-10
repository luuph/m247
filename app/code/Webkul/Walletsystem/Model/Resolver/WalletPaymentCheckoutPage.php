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
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * WalletPaymentCheckoutPage resolver, used for GraphQL request processing.
 */
class WalletPaymentCheckoutPage implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory
     */
    private $walletrecordModel;

    /**
     * Constructor function
     *
     * @param \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel
     */
    public function __construct(
        \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel
    ) {
        $this->walletrecordModel = $walletrecordModel;
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
            $response=[];
            $remainAmount = 0;
            $leftAmount = 0;
            $walletRecordCollection = $this->walletrecordModel->create()
                ->addFieldToFilter('customer_id', ['eq' => $args['customerId']]);
            foreach ($walletRecordCollection as $record) {
                if ($args['productPrice'] <= $record->getRemainingAmount()) {

                    $remainAmount = $record->getRemainingAmount() - $args['productPrice'];
                    $leftAmount = 0;

                } else {
                    $leftAmount = $args['productPrice'] - $record->getRemainingAmount();
                    $remainAmount = 0;
                }
                $response['PaymentToBeMade'] = $args['productPrice'];
                $response['AmountInYourWallet'] = $record->getRemainingAmount();
                $response['RemainingAmount'] = $remainAmount;
                $response['LeftAmountTobePaid'] = $leftAmount;
            }
                return $response;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $lastTransactionDetails;
    }
}
