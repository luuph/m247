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
 * AdjustAmountToWallet resolver, used for GraphQL request processing.
 */
class AdjustAmountToWallet implements ResolverInterface
{
   
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory
     */
    private $walletrecordModel;

    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var \Webkul\Walletsystem\Model\WalletUpdateData
     */
    private $walletUpdate;
    
    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel
     * @param \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Model\WalletUpdateData $walletUpdate
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel,
        \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Model\WalletUpdateData $walletUpdate,
    ) {
        $this->walletHelper = $walletHelper;
        $this->walletrecordModel = $walletrecordModel;
        $this->walletTransaction = $transactionFactory;
        $this->walletUpdate = $walletUpdate;
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
            $responseMessage=[];
            $successCounter = 0;
            $message = '';
            $params = $args;
            $result = [];
            $walletTransaction  = $this->walletTransaction->create();

            if (!array_key_exists('customerIds', $params) || $params['customerIds'] == '{}') {
                $message = __('Please select Customers to add amount.');
            }

            if (!array_key_exists('walletamount', $params) && $params['walletamount']== '' &&
             $params['walletamount'] <= 0) {
                $responseMessage['message'] = "Please Enter a valid amount to add";
                return $responseMessage;
            }

            $selectcustomerIds = $params['customerIds'];
            $customerIdArray = explode(",", $selectcustomerIds);
            array_unshift($customerIdArray, "");
            unset($customerIdArray[0]);
            $currencycode = $this->walletHelper->getBaseCurrencyCode();
            $params['curr_code'] = $currencycode;
            $params['curr_amount'] = $params['walletamount'];
            $params['sender_id'] = 0;
            $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
            $params['order_id'] = 0;
            $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
            $params['increment_id'] = '';
            $params['walletnote'] = $this->walletHelper->validateScriptTag($params['walletnote']);

            if ($params['walletnote']=='') {
                $walnote = 'Amount'.$params['walletactiontype'].'ed by Admin';
                $params['walletnote'] = $walnote;
            }

            foreach ($customerIdArray as $customerId) {
                try {
                    if ($params['walletactiontype']==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                        $result = $this->walletUpdate->debitAmount($customerId, $params);
                    } else {
                        $walletRecordModel = $this->walletUpdate->getRecordByCustomerId($customerId);
                        $remainingAmount = 0;
                        if ($walletRecordModel!= '' && $walletRecordModel->getEntityId()) {
                            $remainingAmount = $walletRecordModel->getRemainingAmount();
                        }
                        if (($remainingAmount + $params['walletamount']) > 99999999.9999) {
                            $amt = $currencycode.'100000000';
                            $message = 'The maximum limit to have in wallet is '
                                .$amt.' for customer id '.$customerId;
                            continue;
                        }
                        $result = $this->walletUpdate->creditAmount($customerId, $params);
                    }
                } catch (\Exception $e) {
                    $message = $e;
                }
                
                if (array_key_exists('success', $result)) {
                    $successCounter++;
                }
            }
            if ($successCounter > 0) {
                $message = "Total of'.$successCounter.' Customer(s) wallet are updated";
            }
            $responseMessage['message'] = $message ;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }
}
