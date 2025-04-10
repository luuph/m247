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

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\Wallettransaction;

/**
 * Webkul Walletsystem Class
 */
class Bankamount extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $walletHelper;

    /**
     * @var \Webkul\Walletsystem\Model\WalletUpdateData
     */
    
    protected $walletUpdate;
    /**
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    protected $walletNotification;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\WalletNotification $walletNotification
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param WalletUpdateData $walletUpdate
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        WalletUpdateData $walletUpdate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletNotification = $walletNotification;
        $this->walletHelper = $walletHelper;
        $this->scopeConfig = $scopeConfig;
        $this->walletUpdate = $walletUpdate;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return string
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/index/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Validate params
     *
     * @param arary $params
     * @return string
     */
    protected function validateParams($params)
    {
        if (!empty($params) && is_array($params)) {
            if (array_key_exists('customer_id', $params) && $params['customer_id']!='') {
                if (array_key_exists('amount', $params) &&
                $params['amount']!='' &&
                array_key_exists('bank_details', $params) &&
                $params['bank_details']!=''
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['walletnote'])
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['bank_details'])
                ) {
                    $params['walletnote'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $params['walletnote']);
                    $params['bank_details'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $params['bank_details']
                    );
                    $baseCurrencyCode = $this->walletHelper->getBaseCurrencyCode();
                    $currencycode = $this->walletHelper->getCurrentCurrencyCode();
                    $amount = $params['amount'];
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $currencycode,
                        $baseCurrencyCode,
                        $amount
                    );
                    $customerId = $params['customer_id'];
                    $params['curr_code'] = $currencycode;
                    $params['curr_amount'] = $params['amount'];
                    $params['order_id'] = 0;
                    $params['status'] = Wallettransaction::WALLET_TRANS_STATE_PENDING;
                    $params['increment_id'] = '';
                    $params['walletamount'] = $baseAmount;
                    $params['walletactiontype'] = 'debit';
                    $params['sender_id'] = 0;
                    $params['sender_type'] = Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE;
                    $params['transfer_to_bank'] = 1;
                    if ($params['walletnote']=='') {
                        $params['walletnote'] =
                        __(
                            '%1, Amount is transferred by customer to bank account',
                            $params['amount']
                        );
                    }
                    $customerId = $params['customer_id'];
                    $result = $this->walletUpdate->debitAmount($customerId, $params);
                    if (is_array($result) && array_key_exists('success', $result)) {
                        $this->setNotificationMessageForAdmin();
                        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        if ($this->scopeConfig->getValue(
                            'walletsystem/message_after_request/show_message',
                            $storeScope
                        )
                            ) {
                            $message = $this->scopeConfig->getValue(
                                'walletsystem/message_after_request/message_content',
                                $storeScope
                            );
                            $this->messageManager->addSuccess(__($message));
                        } else {
                            $this->messageManager->addSuccess(__("Amount transfer request has been sent!"));
                        }
                    }
                } else {
                    $this->messageManager->addError(__("Something went wrong, please try again"));
                }
            } else {
                $this->messageManager->addError(__("Something went wrong, please try again"));
            }
        } else {
            $this->messageManager->addError(__("Something went wrong, please try again"));
        }
    }

    /**
     * Send notification message to admin
     */
    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->walletNotification->setBanktransferCounter(1);
            $this->walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setBanktransferCounter($notification->getBanktransferCounter()+1);
            }
        }
        $notificationModel->save();
    }
}
