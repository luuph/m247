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

namespace Webkul\Walletsystem\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;

/**
 * Webkul Walletsystem Class
 */
class WalletUpdateData extends AbstractModel
{
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $walletrecord;
    
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction $resource
     * @param WalletrecordFactory $walletrecord
     * @param WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction $resource,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->timezoneInterface = $timezoneInterface;
        $this->mailHelper = $mailHelper;
        $this->messageManager = $messageManager;
        $this->localeDate = $localeDate;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get Record By Customer Id
     *
     * @param int $customerId
     * @return collection
     */
    public function getRecordByCustomerId($customerId)
    {
        $recordId = 0;
        $walletRecordModel = '';
        $walletRecordCollection = $this->walletrecord
            ->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($walletRecordCollection as $walletRecord) {
            $recordId = $walletRecord->getEntityId();
        }
        if ($recordId) {
            $walletRecordModel = $this->walletrecord
                ->create()
                ->load($recordId);
        }
        return $walletRecordModel;
    }

    /**
     * Credit Amount from customer's wallet
     *
     * @param int $customerId
     * @param array $params
     * @return array
     */
    public function creditAmount($customerId, $params)
    {
        $this->setTransactionModelData($customerId, $params);
        return [
            'success' => 1
        ];
    }

    /**
     * Debit Amount from customer's wallet
     *
     * @param int $customerId
     * @param array $params
     * @return array
     */
    public function debitAmount($customerId, $params)
    {
        $customerRecord = $this->getRecordByCustomerId($customerId);
        if ($customerRecord!='' &&
            $customerRecord->getEntityId() &&
            $customerRecord->getRemainingAmount() >= $params['walletamount']) {
            $this->setTransactionModelData($customerId, $params);
            return [
                'success' => 1
            ];
        } else {
            $this->messageManager->addError(
                __(
                    "Respective amount is not available your wallet",
                    $customerId
                )
            );
            return [
                'error' => 1
            ];
        }
    }

    /**
     * Set Transaction Model Data customer's wallet
     *
     * @param int $customerId
     * @param array $params
     */
    public function setTransactionModelData($customerId, $params)
    {
        $currencycode = $this->walletHelper->getBaseCurrencyCode();
        $walletTransactionModel = $this->walletTransaction->create();
        $walletTransactionModel->setCustomerId($customerId)
            ->setAmount($params['walletamount'])
            ->setCurrAmount($params['curr_amount'])
            ->setStatus($params['status'])
            ->setCurrencyCode($params['curr_code'])
            ->setAction($params['walletactiontype'])
            ->setTransactionNote($params['walletnote'])
            ->setSenderId($params['sender_id'])
            ->setSenderType($params['sender_type'])
            ->setOrderId($params['order_id']);
        if (array_key_exists('bank_details', $params)) {
            $walletTransactionModel->setBankDetails($params['bank_details']);
        }
        $walletTransactionModel->save();
        $walletRecordModel = $this->getRecordByCustomerId($customerId);
        if ($walletRecordModel!='' && $walletRecordModel->getEntityId()) {
            $remainingAmount = $walletRecordModel->getRemainingAmount();
            $totalAmount = $walletRecordModel->getTotalAmount();
            $usedAmount = $walletRecordModel->getUsedAmount();
            $recordId = $walletRecordModel->getEntityId();
            if ($params['walletactiontype']=='debit') {
                $data = [
                    'used_amount' => $usedAmount + $params['walletamount'],
                    'remaining_amount' => $remainingAmount - $params['walletamount'],
                    'updated_at' => $this->date->gmtDate(),
                ];
                $finalAmount = $remainingAmount - $params['walletamount'];
            } else {
                $data = [
                    'total_amount' => $params['walletamount'] + $totalAmount,
                    'remaining_amount' => $params['walletamount'] + $remainingAmount,
                    'updated_at' => $this->date->gmtDate()
                ];
                $finalAmount = $params['walletamount'] + $remainingAmount;
            }
            if ($params['status']==$walletTransactionModel::WALLET_TRANS_STATE_APPROVE ||
                (array_key_exists('transfer_to_bank', $params)
                && $params['transfer_to_bank']==1)) {
                    $walletRecordModel = $this->walletrecord->create()
                    ->load($recordId)
                    ->addData($data)
                    ->save();
            }
        } else {
            if ($params['status']==$walletTransactionModel::WALLET_TRANS_STATE_APPROVE ||
                (array_key_exists('transfer_to_bank', $params)
                && $params['transfer_to_bank']==1)) {
                $walletRecordModel = $this->walletrecord->create();
                $walletRecordModel->setTotalAmount($params['walletamount'])
                    ->setCustomerId($customerId)
                    ->setRemainingAmount($params['walletamount'])
                    ->setUpdatedAt($this->date->gmtDate());
                $saved = $walletRecordModel->save();
            }
            $finalAmount = $params['walletamount'];
        }
        if ($params['status']==$walletTransactionModel::WALLET_TRANS_STATE_APPROVE ||
            (array_key_exists('transfer_to_bank', $params)
            && $params['transfer_to_bank']==1)) {
                $formattedDate = $this->localeDate->date()
                                    ->format('Y-m-d H:i:s');
                $emailParams = [
                    'walletamount' => $this->walletHelper->getformattedPrice($params['walletamount']),
                    'remainingamount' => $this->walletHelper->getformattedPrice($finalAmount),
                    'type' => $params['sender_type'],
                    'action' => $params['walletactiontype'],
                    'increment_id' => $params['increment_id'],
                    'transaction_at' => $formattedDate,
                    'walletnote' => strip_tags($params['walletnote']),
                    'sender_id' => $params['sender_id'],
                ];
                $customerId = isset($params['customerid'])?$params['customerid']:$customerId;
                $store = $this->walletHelper->getStore();
                $this->mailHelper->sendMailForTransaction(
                    $customerId,
                    $emailParams,
                    $store
                );
        }
    }
}
