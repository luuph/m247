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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Walletsystem Class
 */
class CustomerDeleteCommitAfter implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Walletsystem\Model\WalletPayeeFactory
     */
    protected $walletPayee;

    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;

    /**
     * @var \Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $walletRecord;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param \Webkul\Walletsystem\Model\WallettransactionFactory $walletTransaction
     * @param \Webkul\Walletsystem\Model\WalletrecordFactory $walletRecord
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Webkul\Walletsystem\Model\WallettransactionFactory $walletTransaction,
        \Webkul\Walletsystem\Model\WalletrecordFactory $walletRecord
    ) {
        $this->helper = $helper;
        $this->walletPayee = $walletPayee;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecord = $walletRecord;
    }

    /**
     * Customer delete event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $this->deletePayeeFromList($customerId);
        $this->deleteTransactionForCustomer($customerId);
        $this->deleteRecordForCustomer($customerId);
        return $this;
    }

    /**
     * Delete payee from list
     *
     * @param int $customerId
     */
    protected function deletePayeeFromList($customerId)
    {
        $walletPayeeCollection = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('payee_customer_id', $customerId);
        if ($walletPayeeCollection->getSize()) {
            foreach ($walletPayeeCollection as $payee) {
                $this->deleteObject($payee);
            }
        }
    }

    /**
     * Delete transaction for customer
     *
     * @param int $customerId
     */
    protected function deleteTransactionForCustomer($customerId)
    {
        $walletTransactionCollection = $this->walletTransaction->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletTransactionCollection->getSize()) {
            foreach ($walletTransactionCollection as $walletTransactionData) {
                $this->deleteObject($walletTransactionData);
            }
        }
    }

    /**
     * Delete record for customer
     *
     * @param int $customerId
     */
    protected function deleteRecordForCustomer($customerId)
    {
        $walletRecordCollection = $this->walletRecord->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $walletRecordData) {
                $this->deleteObject($walletRecordData);
            }
        }
    }

    /**
     * Performs delete operation on objects
     *
     * @param object $object
     */
    public function deleteObject($object)
    {
        $object->delete();
    }
}
