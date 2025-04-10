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
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Walletsystem\Model\Wallettransaction;
use Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory;

/**
 * Webkul Walletsystem Class
 */
class MasscancelBanktransfer extends WalletController
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var WallettransactionAdditionalDataFactory
     */
    public $walletTransAddData;
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    public $websiteRepositiry;
    /**
     * @var \Webkul\Walletsystem\Controller\Adminhtml\Wallet\Disapprove
     */
    public $disapprove;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    public $walletHelper;
    /**
     * @var WalletUpdateData
     */
    public $walletUpdate;
    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory
     */
    public $walletTransaction;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param Filter $filter
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry
     * @param WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Controller\Adminhtml\Wallet\Disapprove $disapprove
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
     * @param WalletUpdateData $walletUpdate
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param WallettransactionAdditionalDataFactory $walletTransAddData
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Controller\Adminhtml\Wallet\Disapprove $disapprove,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory,
        WalletUpdateData $walletUpdate,
        \Webkul\Walletsystem\Helper\Data $helper,
        WallettransactionAdditionalDataFactory $walletTransAddData
    ) {
        $this->walletTransAddData = $walletTransAddData;
        parent::__construct($context);
        $this->websiteRepositiry = $websiteRepositiry;
        $this->disapprove = $disapprove;
        $this->walletHelper = $helper;
        $this->filter = $filter;
        $this->walletUpdate = $walletUpdate;
        $this->walletTransaction = $transactionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $this->refundAmountToWallet($this->getRequest()->getParams());
            if (isset($data['selected'])) {
                $selected = count($data['selected']);
            } else {
                $selected = __("All Selected");
            }
            
            $status = Wallettransaction::WALLET_TRANS_STATE_CANCEL;
            $collection = $this->filter->getCollection($this->collectionFactory->create())
                                        ->addFieldToFilter('bank_details', ["neq" => ""]);
            $totalCount = $collection->getSize();
            $collection = $collection->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
            $entityIds = $collection->getAllIds();
            $pendingTransCount = count($entityIds);
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $additionalDataCollection = $this->walletTransAddData
                                                ->create()
                                                ->getCollection()
                                                ->addFieldToFilter('transaction_id', $id)
                                                ->getFirstItem();
                    if ($additionalDataCollection && $additionalDataCollection->getSize()) {
                        $additionalDataCollection->setData("additional", $data["reason"]);
                        $additionalDataCollection->save();
                    } else {
                        $additionalData = $this->walletTransAddData->create();
                        $additionalData->setData("additional", $data["reason"]);
                        $additionalData->setData("transaction_id", (int)$id);
                        $additionalData->save();
                    }
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                if (($totalCount-$pendingTransCount) > 0) {
                    $this->messageManager->addSuccess(
                        __('%1 record(s) successfully updated.', $pendingTransCount)
                    );
                    $this->messageManager->addError(
                        __('%1 record(s) cannot be updated.', ($totalCount-$pendingTransCount))
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __('%1 record(s) successfully updated.', $selected)
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('%1 record(s) cannot be updated.', $selected)
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __($e->getMessage())
            );
        }
        return $resultRedirect->setPath(
            '*/*/bankdetails',
            ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
        );
    }

    /**
     * Refund amount to wallet
     *
     * @param array $params
     */
    public function refundAmountToWallet($params)
    {
        if (isset($params['selected'])) {
            $ids = $params['selected'];
            $this->refundSelectedTransactions($ids);
        } else {
            $this->refundAllPendingTransactions();
        }
    }

    /**
     * Refund all pending transactions
     */
    public function refundAllPendingTransactions()
    {
        $txns = $this->walletTransaction->create()->getCollection()
                                ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
    }

    /**
     * Refund selected transactions
     *
     * @param array $ids
     */
    public function refundSelectedTransactions($ids)
    {
        $txns = $this->walletTransaction->create()->getCollection()
                                ->addFieldToFilter('entity_id', ['in'=>$ids])
                                ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
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
        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."walletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();
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
