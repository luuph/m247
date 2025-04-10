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
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\WallettransactionFactory;

/**
 * Webkul Walletsystem Class
 */
class Massaddamount extends WalletController
{
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    private $walletrecord;

    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    private $mailHelper;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    private $walletUpdate;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param WalletrecordFactory $walletrecord
     * @param WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
     * @param WalletUpdateData $walletUpdate
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->mailHelper = $mailHelper;
        $this->walletUpdate = $walletUpdate;
        $this->jsonDecoder = $jsonDecoder;
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
        if (!array_key_exists('wkcustomerids', $params) || $params['wkcustomerids'] == '{}') {
            $this->messageManager->addError(
                __('Please select Customers to add amount.')
            );
            return $resultRedirect->setPath('walletsystem/wallet/addamount');
        }
        if (array_key_exists('walletamount', $params) &&
            $params['walletamount']!= '' &&
            $params['walletamount'] > 0) {
            $customerIds = array_flip($this->jsonDecoder->decode($params['wkcustomerids']));
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
                $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
            }
            foreach ($customerIds as $customerId) {
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
                            $this->messageManager->addError(
                                __('The maximum limit to have in wallet is '.$amt.' for customer id '.$customerId)
                            );
                            continue;
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
                    $successCounter++;
                }
            }

            if ($successCounter > 0) {
                $this->messageManager->addSuccess(
                    __("Total of %1 Customer(s) wallet are updated", $successCounter)
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please Enter a valid amount to add.')
            );
        }
        return $resultRedirect->setPath('walletsystem/wallet/addamount');
    }
}
