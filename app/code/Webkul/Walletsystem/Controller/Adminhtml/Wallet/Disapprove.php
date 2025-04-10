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

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\Wallettransaction;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory;

/**
 * Webkul Walletsystem Class
 */
class Disapprove extends WalletController
{
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $walletrecord;

    /**
     * @var WallettransactionAdditionalDataFactory
     */
    protected $walletTransAddData;

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
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepositiry;

   /**
    * Constructor
    *
    * @param Action\Context $context
    * @param WalletrecordFactory $walletrecord
    * @param WallettransactionFactory $transactionFactory
    * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry
    * @param \Webkul\Walletsystem\Helper\Data $walletHelper
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
    * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
    * @param WalletUpdateData $walletUpdate
    * @param WallettransactionAdditionalDataFactory $walletTransAddData
    */
    public function __construct(
        Action\Context $context,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        WalletUpdateData $walletUpdate,
        WallettransactionAdditionalDataFactory $walletTransAddData
    ) {
        $this->walletTransAddData = $walletTransAddData;
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->mailHelper = $mailHelper;
        $this->websiteRepositiry = $websiteRepositiry;
        $this->walletUpdate = $walletUpdate;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $successCounter = 0;
        $params = $this->getRequest()->getParams();

        $walletTransactionModel = $this->walletTransaction->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        $statusCollection = $this->walletTransaction
                                    ->create()
                                    ->getCollection()
                                    ->addFieldToFilter('entity_id', $params['entity_id'])
                                    ->addFieldToFilter('status', $walletTransactionModel::WALLET_TRANS_STATE_PENDING);

        if (is_array($params) && array_key_exists('entity_id', $params) &&
            $params['entity_id'] != '' && $statusCollection->getSIze()) {
            $condition = "`entity_id`=".$params['entity_id'];
            foreach ($statusCollection as $status) {
                $status = $status->getStatus();
            }
            if ($status == $walletTransactionModel::WALLET_TRANS_STATE_CANCEL) {
                $this->messageManager->addSuccess(
                    __('No change in status.')
                );
                return $resultRedirect->setPath(
                    '*/*/bankdetails',
                    ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
                );
            }
            if (!isset($params["reason"])) {
                $this->messageManager->addError(
                    __('Something went wrong, please try again.')
                );
                return $resultRedirect->setPath(
                    'walletsystem/wallet/index'
                );
            }
            $additionalData = $this->walletTransAddData->create();
            $additionalData->setData("additional", $params["reason"]);
            $additionalData->setData("transaction_id", $params["entity_id"]);
            $additionalData->save();
            $this->walletTransaction->create()->getCollection()->setTableRecords(
                $condition,
                ['status' => $walletTransactionModel::WALLET_TRANS_STATE_CANCEL]
            );
            $this->creditAmountToCustomerWallet($params['entity_id']);
            $this->messageManager->addSuccess(
                __('Transaction status is updated.')
            );
        } else {
            $this->messageManager->addError(
                __('Transaction status cannot be changed.')
            );
            return $resultRedirect->setPath(
                'walletsystem/wallet/bankdetails',
                ['sender_type'=>$walletTransactionModel::CUSTOMER_TRANSFER_BANK_TYPE]
            );
        }
        return $resultRedirect->setPath(
            'walletsystem/wallet/view',
            ['entity_id'=>$params['entity_id']]
        );
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
