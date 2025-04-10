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
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * AdminCancelTransaction resolver, used for GraphQL request processing.
 */
class AdminCancelTransaction implements ResolverInterface
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
     * @var \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory
     */
    private $wallettransactionModel;

    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory
     */
    private $walletTransAddData;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepositiry;

    /**
     * @var WalletUpdateData
     */
    private $walletUpdate;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel
     * @param \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory $walletTransAddData
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry
     * @param WalletUpdateData $walletUpdate
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel,
        \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory $walletTransAddData,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        WalletUpdateData $walletUpdate
    ) {
        $this->walletHelper = $walletHelper;
        $this->wallettransactionModel = $wallettransactionModel;
        $this->walletTransaction = $transactionFactory;
        $this->walletTransAddData = $walletTransAddData;
        $this->websiteRepositiry = $websiteRepositiry;
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
        $message = '';
        try {
            $responseMessage=[];
            $params = $args;
            
            $successCounter = 0;
            $walletTransactionModel = $this->walletTransaction->create();
            $statusCollection = $this->walletTransaction
                                    ->create()
                                    ->getCollection()
                                    ->addFieldToFilter('entity_id', $params['transactionId'])
                                    ->addFieldToFilter('status', $walletTransactionModel::WALLET_TRANS_STATE_PENDING);
            if (is_array($params) && array_key_exists('transactionId', $params) &&
            $params['transactionId'] != '' && $statusCollection->getSIze()) {
                $condition = "`entity_id`=".$params['transactionId'];
                foreach ($statusCollection as $status) {
                    $status = $status->getStatus();
                }
                if ($status == $walletTransactionModel::WALLET_TRANS_STATE_CANCEL) {
                    $message = __('No change in status.');
                }
                if (!isset($params["reason"])) {
                    $message = __('Something went wrong, please try again.');
                }
                $additionalData = $this->walletTransAddData->create();
                $additionalData->setData("additional", $params["reason"]);
                $additionalData->setData("transaction_id", $params["transactionId"]);
                $additionalData->save();
                $this->walletTransaction->create()->getCollection()->setTableRecords(
                    $condition,
                    ['status' => $walletTransactionModel::WALLET_TRANS_STATE_CANCEL]
                );
                $this->creditAmountToCustomerWallet($params['transactionId']);
                $message = __('Transaction status is updated.');
            } else {
                $message = __('Transaction status cannot be changed.');
            }
            $responseMessage['message'] = $message ;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }

    /**
     * Credit amount to customer wallet
     *
     * @param int $txnId
     */
    public function creditAmountToCustomerWallet($txnId)
    {
        $walletTransaction  = $this->walletTransaction->create();
        $txnDetails = $walletTransaction->getCollection()
        ->addFieldToFilter('entity_id', $txnId);
        foreach ($txnDetails as $txnDetails) {
            $txn = $txnDetails;
        }
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();
        
        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."walletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";
        $currencycode = $this->walletHelper->getBaseCurrencyCode();
        $params['curr_code'] = $currencycode;
        $params['walletactiontype'] = "credit";
        $params['curr_amount'] = $amount;
        $params['walletamount'] = $amount;
        $params['sender_id'] = 0;
        $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
        $params['order_id'] = 0;
        $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
        $params['increment_id'] = '';
        $params['customerid'] = $customerId;
        $params['walletnote'] = __("Request To transfer amount to Bank is cancelled").$link;
        $result = $this->walletUpdate->creditAmount($customerId, $params);
    }
}
