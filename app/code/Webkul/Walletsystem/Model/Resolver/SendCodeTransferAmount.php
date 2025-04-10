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
use Webkul\Walletsystem\Helper\Mail as WalletMail;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

/**
 * SendCodeTransferAmount resolver, used for GraphQL request processing.
 */
class SendCodeTransferAmount implements ResolverInterface
{
    public const  SYMBOLS_COLLECTION = '0123456789';
    public const  DEFAULT_LENGTH = 6;

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
     * @var \Webkul\Walletsystem\Model\WalletPayeeFactory
     */
    private $walletPayee;

    /**
     * @var WalletMail
     */
    private $walletMail;

    /**
     * @var \Webkul\Walletsystem\Model\WalletTransferData
     */
    private $walletTransfer;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    private $base64;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param WalletMail $walletMail
     * @param \Webkul\Walletsystem\Model\WalletTransferData $walletTransfer
     * @param Encryptor $encryptor
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        WalletMail $walletMail,
        \Webkul\Walletsystem\Model\WalletTransferData $walletTransfer,
        Encryptor $encryptor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64
    ) {
        $this->walletHelper = $walletHelper;
        $this->walletPayee = $walletPayee;
        $this->walletMail = $walletMail;
        $this->walletTransfer = $walletTransfer;
        $this->encryptor = $encryptor;
        $this->date = $date;
        $this->base64 = $base64;
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
            $result ='';
            $message = '';
            $walletHelper = $this->walletHelper;
            $params = $args;
            $result = $this->validatePayee($args);
            if (!$result) {
                $message = "Payee is not validate";
               
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
            if ($amount != 0 && $params['receiverId'] != 0 && $params['receiverId'] != '') {
                $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                $totalAmount = $walletHelper->getWalletTotalAmount($params['senderId']);
                if ($transferAmount <= $totalAmount) {
                    $params['base_amount'] = $transferAmount;
                     $data = $this->sendEmailForCode($params);
                       
                    $sessionData = [
                        'sender_id' => $data['customer_id'],
                        'reciever_id' => $params['receiverId'],
                        'code' => $this->createCodeHash($data['code']),
                        'amount' => $params['amount'],
                        'base_amount' => $transferAmount,
                        'walletnote' => $params['walletnote'],
                        'created_at' => strtotime($this->date->gmtDate())
                    ];

                    $serializedData = $walletHelper->convertStringAccToVersion($sessionData, 'encode');
                    $this->walletTransfer->setWalletTransferDataToSession($serializedData);
                    $status = 1;
                    unset($sessionData['code']);
                    $getParamData = urlencode($this->base64->serialize($sessionData));
                    $sendData = [
                        'parameter' => $getParamData
                    ];
                    $message = "Code has been successfully generated";

                    $responseMessage['transfercode'] = $data['code'];
                    $responseMessage['message'] =$message;
                }
            }
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
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
            ->addFieldToFilter("payee_customer_id", $params['receiverId'])
            ->addFieldToFilter("status", $walletPayeeModel::PAYEE_STATUS_ENABLE)
            ->addFieldToFilter("customer_id", $params['senderId']);
        if ($walletPayeeCollection->getSize()) {
            return true;
        }
        return false;
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
}
