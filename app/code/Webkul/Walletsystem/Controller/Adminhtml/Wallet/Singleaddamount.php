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
class Singleaddamount extends WalletController
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $walletTransaction  = $this->walletTransaction->create();
        if (array_key_exists('customerid', $params) &&
            $params['customerid'] != '') {
            if (array_key_exists('walletamount', $params) &&
                $params['walletamount']!= '' &&
                $params['walletamount'] > 0) {
                $currencycode = $this->walletHelper->getBaseCurrencyCode();
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $params['walletamount'];
                $params['sender_id'] = 0;
                $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
                $params['order_id'] = 0;
                $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
                $params['increment_id'] = '';
                $customerId = $params['customerid'];
                $totalAmount = 0;
                $remainingAmount = 0;
                $params['walletnote'] = $this->walletHelper->validateScriptTag($params['walletnote']);
                if ($params['walletnote']=='') {
                    $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
                }
                try {
                    if ($params['walletactiontype']==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                        $result = $this->walletUpdate->debitAmount($customerId, $params);
                    } else {
                        $walletRecordModel = $this->walletUpdate->getRecordByCustomerId($customerId);
                        if ($walletRecordModel != '' && $walletRecordModel->getEntityId()) {
                            $remainingAmount = $walletRecordModel->getRemainingAmount();
                        }
                        if (($remainingAmount + $params['walletamount']) > 99999999.9999) {
                            $this->messageManager->addError(
                                __('The maximum limit to have in wallet is '.$currencycode.'100000000.')
                            );
                            return $resultRedirect->setPath('walletsystem/wallet/addamount');
                        }
                        $result = $this->walletUpdate->creditAmount($customerId, $params);
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('Please Enter a valid amount to add.')
                    );
                    return $resultRedirect->setPath('walletsystem/wallet/addamount');
                }

                if (array_key_exists('success', $result)) {
                    $this->messageManager->addSuccess(
                        __("Amount is successfully updated in customer's wallet.")
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid amount to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select Customers to add amount.')
            );
        }
        return $resultRedirect->setPath('walletsystem/wallet/addamount');
    }
}
