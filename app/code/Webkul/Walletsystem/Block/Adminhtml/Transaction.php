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

namespace Webkul\Walletsystem\Block\Adminhtml;

use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order;
use Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory;

/**
 * Webkul Walletsystem Class
 */
class Transaction extends \Magento\Backend\Block\Template
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var [WallettransactionFactory]
     */
    private $walletTransaction;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param Order                                             $order
     * @param WallettransactionFactory                          $wallettransactionFactory
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Webkul\Walletsystem\Helper\Data                  $walletHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param WallettransactionAdditionalDataFactory            $walletTransAddData
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Order $order,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        WallettransactionAdditionalDataFactory $walletTransAddData,
        array $data = []
    ) {
        $this->walletTransAddData = $walletTransAddData;
        $this->order = $order;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->walletHelper = $walletHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Get order object
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get transaction data
     *
     * @return object
     */
    public function getTransactionData()
    {
        $id = $this->getRequest()->getParam('entity_id');
        return $this->walletTransaction->create()->load($id);
    }

    /**
     * Get customer data by id
     *
     * @param int $customerId
     * @return object
     */
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get transaction amount
     *
     * @param object $transactionData
     * @return string
     */
    public function getTransactionAmount($transactionData)
    {
        $amount = $transactionData->getAmount();
        $currencyCode = $this->walletHelper->getCurrentCurrencyCode();
        $precision = 2;
        return $this->priceCurrency->format(
            $amount,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currencyCode
        );
    }

    /**
     * Get formatted date
     *
     * @param Date $date
     * @return Date
     */
    public function getFormattedDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    /**
     * Get transaction Reason data in case of Cancelled Bank Transaction
     *
     * @return object
     */
    public function getTransactionAdditionalData()
    {
        $id = $this->getRequest()->getParam('entity_id');
        
        $collections = $this->walletTransAddData->create()
                                                ->getCollection()
                                                ->addFieldToFilter("transaction_id", $id)
                                                ->getFirstItem();
        return $collections->getAdditional();
    }
}
