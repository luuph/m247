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
use Webkul\Walletsystem\Helper\Data as WalletHelper;
use Webkul\Walletsystem\Helper\Mail as WalletMail;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Webkul\Walletsystem\Model\WalletTransferData;

/**
 * Webkul Walletsystem Class
 */
class SendCode extends \Magento\Customer\Controller\AbstractAccount
{
    public const  SYMBOLS_COLLECTION = '0123456789';

    /**
     * The minimum length of the default
     */
    public const  DEFAULT_LENGTH = 6;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;

    /**
     * @var Webkul\Walletsystem\Helper\Mail
     */
    protected $walletMail;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Webkul\Walletsystem\Model\WalletTransferData
     */
    protected $waletTransfer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Walletsystem\Model\WalletPayeeFactory
     */
    protected $walletPayee;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    protected $base64;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param WalletHelper $walletHelper
     * @param WalletMail $walletMail
     * @param Encryptor $encryptor
     * @param WalletTransferData $walletTransfer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WalletHelper $walletHelper,
        WalletMail $walletMail,
        Encryptor $encryptor,
        WalletTransferData $walletTransfer,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletMail = $walletMail;
        $this->encryptor = $encryptor;
        $this->waletTransfer = $walletTransfer;
        $this->date = $date;
        $this->walletPayee = $walletPayee;
        $this->base64 = $base64;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $walletHelper = $this->walletHelper;
            $params = $this->getRequest()->getParams();
            $result = $this->validatePayee($params);
            if (!$result) {
                $this->messageManager->addError(__("Payee is not validate"));
                return $this->resultRedirectFactory->create()->setPath(
                    'walletsystem/transfer/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            if (!$walletHelper->getTransferValidationEnable()) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/sendamount',
                    $this->getRequest()->getParams()
                );
            } else {
                if (array_key_exists('created_at', $params)) {
                    if (!$this->updateSession()) {
                        $this->messageManager->addError(__("Session expired for this transaction, please try again"));
                        return $this->resultRedirectFactory->create()->setPath(
                            'walletsystem/transfer/index',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                }
                $status = 0;
                $duration = $walletHelper->getCodeValidationDuration();
                $fromCurrency = $walletHelper->getCurrentCurrencyCode();
                $toCurrency = $walletHelper->getBaseCurrencyCode();
                if (!isset($params['walletnote'])) {
                    $params['walletnote'] = "";
                }
                $params['walletnote'] = $walletHelper->validateScriptTag($params['walletnote']);
                $amount = $params['amount'];
                if ($amount != 0 && $params['reciever_id'] != 0 && $params['reciever_id'] != '') {
                    $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                    $totalAmount = $walletHelper->getWalletTotalAmount($params['sender_id']);
                    if ($transferAmount <= $totalAmount) {
                        $params['base_amount'] = $transferAmount;
                        $data = $this->sendEmailForCode($params);
                        
                        $sessionData = [
                            'sender_id' => $data['customer_id'],
                            'reciever_id' => $params['reciever_id'],
                            'code' => $this->createCodeHash($data['code']),
                            'amount' => $params['amount'],
                            'base_amount' => $transferAmount,
                            'walletnote' => $params['walletnote'],
                            'created_at' => strtotime($this->date->gmtDate())
                        ];
                        $serializedData = $walletHelper->convertStringAccToVersion($sessionData, 'encode');
                        $this->waletTransfer->setWalletTransferDataToSession($serializedData);
                        $status = 1;
                        unset($sessionData['code']);
                        $getParamData = urlencode($this->base64->serialize($sessionData));
                        $sendData = [
                            'parameter' => $getParamData
                        ];
                        $this->messageManager->addSuccess(__("Code has been sent to your email id."));
                        return $this->resultRedirectFactory->create()->setPath(
                            'walletsystem/transfer/verificationCode',
                            ['_secure' => $this->getRequest()->isSecure(), '_query' => $sendData]
                        );
                    } else {
                        $this->messageManager->addError(__("You don't have enough amount in your wallet."));
                    }
                } else {
                    $this->messageManager->addError(__("Please try again with valid data."));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Send email to customer for code
     *
     * @param array $params
     * @return array
     */
    public function sendEmailForCode($params)
    {
        $walletEmail = $this->walletMail;
        $data = [
            'customer_id' => $this->walletHelper->getCustomerId(),
            'amount' => $params['amount'],
            'base_amount' => $params['base_amount'],
            'code' => $this->generateCode(),
            'duration'=> $this->walletHelper->getCodeValidationDuration()
        ];
        $walletEmail->sendTransferCode($data);
        return $data;
    }

    /**
     * Generate code to send
     *
     * @return string
     */
    public function generateCode()
    {
        $alphabet = self::SYMBOLS_COLLECTION;
        $length = self::DEFAULT_LENGTH;
        $code = '';
        for ($i = 0, $indexMax = strlen($alphabet) - 1; $i < $length; ++$i) {
            $code .= substr($alphabet, random_int(0, $indexMax), 1);
        }
        return $code;
    }

    /**
     * Create hash code
     *
     * @param string $code
     * @return string
     */
    protected function createCodeHash($code)
    {
        return $this->encryptor->getHash($code, true);
    }

    /**
     * Check session code value espires or not
     *
     * @return boolean
     */
    public function updateSession()
    {
        $this->waletTransfer->checkAndUpdateSession();
        $walletTransferData = $this->waletTransfer->getWalletTransferDataToSession();
        if ($walletTransferData=='') {
            return false;
        }
        return true;
    }

    /**
     * Validate payee
     *
     * @param array $params
     * @return bool
     */
    public function validatePayee($params)
    {
        $walletPayeeModel = $this->walletPayee->create();
        $walletPayeeCollection = $this->walletPayee->create()
            ->getCollection()
            ->addFieldToFilter("payee_customer_id", $params['reciever_id'])
            ->addFieldToFilter("status", $walletPayeeModel::PAYEE_STATUS_ENABLE)
            ->addFieldToFilter("customer_id", $params['sender_id']);
        if ($walletPayeeCollection->getSize()) {
            return true;
        }
        return false;
    }
}
