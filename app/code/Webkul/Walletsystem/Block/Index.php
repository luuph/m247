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

namespace Webkul\Walletsystem\Block;

use Webkul\Walletsystem\Model\ResourceModel\Wallettransaction;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\ResourceModel\Walletrecord;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Webkul\Walletsystem\Model\WallettransactionAdditionalDataFactory;

/**
 * Webkul Walletsystem Class
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Wallettransaction
     */
    private $wallettransactionModel;

    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Walletrecord
     */
    private $walletrecordModel;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollection;

    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
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
     * @var array
     */
    private $walletRecordData = null;

    /**
     * Initialize dependencies
     *
     * @param MagentoFrameworkViewElementTemplateContext    $context
     * @param WalletrecordCollectionFactory                 $walletrecordModel
     * @param WallettransactionCollectionFactory            $wallettransactionModel
     * @param Order                                         $order
     * @param WebkulWalletsystemHelperData                  $walletHelper
     * @param MagentoFrameworkPricingHelperData             $pricingHelper
     * @param CustomerCollection                            $customerCollection
     * @param WallettransactionFactory                      $wallettransactionFactory
     * @param MagentoCustomerModelCustomerFactory           $customerFactory
     * @param MagentoFrameworkPricingPriceCurrencyInterface $priceCurrency
     * @param WallettransactionAdditionalDataFactory        $walletTransAddData
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        Wallettransaction\CollectionFactory $wallettransactionModel,
        Order $order,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerCollection $customerCollection,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        WallettransactionAdditionalDataFactory $walletTransAddData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletTransAddData = $walletTransAddData;
        $this->walletrecordModel = $walletrecordModel;
        $this->wallettransactionModel = $wallettransactionModel;
        $this->order = $order;
        $this->walletHelper = $walletHelper;
        $this->pricingHelper = $pricingHelper;
        $this->customerCollection = $customerCollection;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Prepare layout
     *
     * @return this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getwalletTransactionCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'walletsystem.wallettransaction.pager'
            )
            ->setCollection(
                $this->getwalletTransactionCollection()
            );
            $this->setChild('pager', $pager);
            $this->getwalletTransactionCollection()->load();
        }

        return $this;
    }

    /**
     * Get Pager Html function
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get wallet record data
     *
     * @param int $customerId
     * @return object
     */
    public function getWalletRecordData($customerId)
    {
        if ($this->walletRecordData==null) {
            $walletRecordCollection = $this->walletrecordModel->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($walletRecordCollection->getSize()) {
                foreach ($walletRecordCollection as $record) {
                    $this->walletRecordData = $record;
                    break;
                }
            }
        }
        return $this->walletRecordData;
    }

    /**
     * Get remaining total of a customer
     *
     * @param int $customerId
     * @return float
     */
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecord = $this->getWalletRecordData($customerId);
        if ($walletRecord && $walletRecord->getEntityId()) {
            $remainingAmount = $walletRecord->getRemainingAmount();
            return $this->pricingHelper
                ->currency($remainingAmount, true, false);
        }
        return $this->pricingHelper
                ->currency(0, true, false);
    }

    /**
     * Get wallet bank details
     *
     * @param int $customerId
     * @return string
     */
    public function getWalletBankDetails($customerId)
    {
        return '';
    }

    /**
     * Get transaction collection of a customer
     *
     * @return object
     */
    public function getwalletTransactionCollection()
    {
        if (!$this->transactioncollection) {
            $customerId = $this->walletHelper
                ->getCustomerId();
            $walletRecordCollection = $this->wallettransactionModel->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('transaction_at', 'DESC');
            $this->transactioncollection = $walletRecordCollection;
        }

        return $this->transactioncollection;
    }

    /**
     * Get order
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get all customer collection in which logged in customer in not included
     *
     * @return collection
     */
    public function getCustomerCollection()
    {
        $customerCollection = $this->customerCollection->create()
            ->addFieldToFilter('entity_id', ['neq' => $this->walletHelper->getCustomerId()]);
        return $customerCollection;
    }

    /**
     * Load transaction with transaction id
     *
     * @return object
     */
    public function getTransactionData()
    {
        $id = $this->getRequest()->getParams();
        return $this->walletTransaction->create()->load($id);
    }

    /**
     * Load customer model by customer id
     *
     * @param int $customerId
     * @return object
     */
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get formatted transaction amount
     *
     * @param object $transactionData
     * @return float
     */
    public function getTransactionAmount($transactionData)
    {
        $amount = $this->walletHelper->getwkconvertCurrency(
            $transactionData->getBaseCurrencyCode(),
            $this->walletHelper->getCurrentCurrencyCode(),
            $transactionData->getAmount()
        );
        return $this->priceCurrency->format($amount);
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

    /**
     * Get store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->walletHelper->getBaseCurrencyCode();
    }
}
