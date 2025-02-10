<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

use Amasty\Rma\Model\Condition\Condition;
use Amasty\Rma\Model\OptionSource\ItemStatus;
use Amasty\Rma\Model\OptionSource\State;
use Amasty\Rma\Model\Reason\Reason;
use Amasty\Rma\Model\Request\Repository;
use Amasty\Rma\Model\Request\Request;
use Amasty\Rma\Model\Request\RequestItem;
use Amasty\Rma\Model\Resolution\Resolution;
use Amasty\Rma\Model\Status\Status;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;

require TESTS_TEMP_DIR . '/../testsuite/Magento/Catalog/_files/product_simple.php';

$objectManager = Bootstrap::getObjectManager();

$addressData = [
    'region' => 'CA',
    'region_id' => '12',
    'postcode' => '11111',
    'lastname' => 'lastname',
    'firstname' => 'firstname',
    'street' => 'street',
    'city' => 'Los Angeles',
    'email' => 'admin@example.com',
    'telephone' => '11111111',
    'country_id' => 'US'
];
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo');

/** @var Item $orderItem */
$orderItem = $objectManager->create(Item::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType('simple');
$orderItem->setSku($product->getSku());
$orderItem->setWeight($product->getWeight());

$storeId = $objectManager->get(StoreManagerInterface::class)
    ->getStore()
    ->getId();
/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setSubtotal(100)
    ->setGrandTotal(50)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(50)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($storeId)
    ->addItem($orderItem)
    ->setPayment($payment);
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

/** @var Status $rmaStatus */
$rmaStatus = $objectManager->create(Status::class);
$rmaStatus->setState(State::AUTHORIZED)
    ->setTitle('Test Status')
    ->save();

/** @var Reason $rmaItemReason */
$rmaItemReason = $objectManager->create(Reason::class);
$rmaItemReason->setTitle('Test Reason')
    ->setStatus(1)
    ->setPosition(1)
    ->save();

/** @var Condition $rmaItemCondition */
$rmaItemCondition = $objectManager->create(Condition::class);
$rmaItemCondition->setTitle('Test Condition')
    ->setPosition(1)
    ->setStatus(1)
    ->save();

/** @var Resolution $rmaItemResolution */
$rmaItemResolution = $objectManager->create(Resolution::class);
$rmaItemResolution->setTitle('Test Resolution')
    ->setPosition(1)
    ->setStatus(1)
    ->save();

/** @var RequestItem $rmaItem */
$rmaItem = $objectManager->create(RequestItem::class);
$rmaItem->setConditionId($rmaItemCondition->getId())
    ->setResolutionId($rmaItemResolution->getId())
    ->setReasonId($rmaItemReason->getId())
    ->setItemStatus(ItemStatus::AUTHORIZED)
    ->setOrderItemId($orderItem->getId())
    ->setQty($orderItem->getQtyOrdered())
    ->setRequestQty($orderItem->getQtyOrdered());

/** @var Request $rma */
$rma = $objectManager->create(Request::class);
$rma->setOrderId($order->getId())
    ->setCustomerName('RmaTestCustomer')
    ->setStatus($rmaStatus->getStatusId())
    ->setStoreId($storeId)
    ->setRequestItems([$rmaItem]);

/** @var Repository $rmaRepository */
$rmaRepository = $objectManager->create(Repository::class);
$rmaRepository->save($rma);
