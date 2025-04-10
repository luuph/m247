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
use Webkul\Walletsystem\Model\WalletPayeeFactory;

/**
 * Webkul Walletsystem Class
 */
class Transfer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Wallettransaction
     */
    private $wallettransactionModel;

    /**
     * @var payeeCollection
     */
    private $payeeCollection;

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
     * @var WalletPayeeFactory
     */
    protected $walletPayee;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Walletrecord\CollectionFactory $walletrecordModel
     * @param Wallettransaction\CollectionFactory $wallettransactionModel
     * @param Order $order
     * @param \Webkul\Walletsystem\Model\AccountDetails $accountDetails
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param CustomerCollection $customerCollection
     * @param WallettransactionFactory $wallettransactionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param WalletPayeeFactory $walletPayee
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        Wallettransaction\CollectionFactory $wallettransactionModel,
        Order $order,
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerCollection $customerCollection,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        WalletPayeeFactory $walletPayee,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletrecordModel = $walletrecordModel;
        $this->wallettransactionModel = $wallettransactionModel;
        $this->order = $order;
        $this->accountDetails = $accountDetails;
        $this->walletHelper = $walletHelper;
        $this->pricingHelper = $pricingHelper;
        $this->customerCollection = $customerCollection;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
        $this->walletPayee = $walletPayee;
    }

    /**
     * Prepare layout
     *
     * @return this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWalletPayeeCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'walletsystem.walletpayee.pager'
            )
            ->setCollection(
                $this->getWalletPayeeCollection()
            );
            $this->setChild('pager', $pager);
            $this->getWalletPayeeCollection()->load();
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
     * @return array
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
     * Get remaining amount of wallet
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
     * Get transaction collection of a customer
     *
     * @return collection
     */
    public function getWalletPayeeCollection()
    {
        if (!$this->payeeCollection) {
            $customerId = $this->walletHelper
                ->getCustomerId();
            $walletPayeeCollection = $this->walletPayee->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId);
            $this->payeeCollection = $walletPayeeCollection;
        }
        return $this->payeeCollection;
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
     * Get wallet payee collection
     *
     * @return collection
     */
    public function getEnabledPayeeCollection()
    {
        $customerId = $this->walletHelper
            ->getCustomerId();
        $walletPayee = $this->walletPayee->create();
        $walletPayeeCollection = $this->walletPayee->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', $walletPayee::PAYEE_STATUS_ENABLE);
        return $walletPayeeCollection;
    }

    /**
     * Load customer model by customer id
     *
     * @param int $customerId
     * @return int
     */
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get user account data
     *
     * @return object
     */
    public function getUserAccountData()
    {
        $customerId = $this->walletHelper->getCustomerId();
        $accountDataColection = $this->accountDetails->getCollection()
                                ->addFieldToFilter('customer_id', $customerId)
                                ->addFieldToFilter('status', ['neq' => 0])
                                ->setOrder('entity_id', 'DSC');
        return $accountDataColection;
    }
}
