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
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * Webkul Walletsystem Class
 */
class Banktransfer extends WalletController
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
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * Initialize dependencies
     *
     * @param ActionContext                          $context
     * @param WalletrecordFactory                    $walletrecord
     * @param WallettransactionFactory               $transactionFactory
     * @param WebkulWalletsystemHelperData           $walletHelper
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     * @param WebkulWalletsystemHelperMail           $mailHelper
     * @param WalletUpdateData                       $walletUpdate
     */
    public function __construct(
        Action\Context $context,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        WalletUpdateData $walletUpdate
    ) {
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->mailHelper = $mailHelper;
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
        $walletTransCollec = $walletTransactionModel
                                ->getCollection()
                                ->addFieldToFilter('entity_id', $params['entity_id'])
                                ->addFieldToFilter('status', $walletTransactionModel::WALLET_TRANS_STATE_PENDING);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (is_array($params) && array_key_exists('entity_id', $params) &&
            $params['entity_id'] != '' && $walletTransCollec->getSize()) {
            $condition = "`entity_id`=".$params['entity_id'];
            $this->walletTransaction->create()->getCollection()->setTableRecords(
                $condition,
                ['status' => $walletTransactionModel::WALLET_TRANS_STATE_APPROVE]
            );
            $sendMessageCollection = $this->walletTransaction->create()->getCollection()
            ->addFieldToFilter('entity_id', $params['entity_id']);
            $this->mailHelper->sendCustomerBulkTransferApproveMail($sendMessageCollection);
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
}
