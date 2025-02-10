<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Observer\QuoteExtension;

use Bss\QuoteExtension\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\AddressFactory;

class SaveAddressBook implements ObserverInterface
{
    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressFactory $addressFactory
     * @param Data $helper
     */
    public function __construct(
        ManageQuoteRepository $manageQuoteRepository,
        CartRepositoryInterface $quoteRepository,
        AddressFactory $addressFactory,
        Data $helper
    ) {
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressFactory = $addressFactory;
        $this->helper = $helper;
    }

    /**
     * Save customer address after register success
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isRequiredAddress()) {
            $customer = $observer->getEvent()->getCustomer();
            $customerId = $customer->getId();
            $r4quotes = $this->manageQuoteRepository->getByCustomerId($customerId);
            if ($r4quotes->getTotalCount()) {
                $r4quoteItems = $r4quotes->getItems();
                $quoteId = null;
                foreach ($r4quoteItems as $r4quoteItem) {
                    $quoteId = $r4quoteItem->getQuoteId();
                    break;
                }
                $this->setCustomerAddress($quoteId, $customerId);
            }
        }
    }

    /**
     * Save customer address book
     *
     * @param int $quoteId
     * @param int $customerId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setCustomerAddress($quoteId, $customerId)
    {
        if ($quoteId) {
            $quote = $this->quoteRepository->get($quoteId);
            $address = $this->addressFactory->create();
            $address->setData($quote->getShippingAddress()->getData());
            $address->setCustomerId($customerId)
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            $address->save();
        }
    }
}
