<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Account\Commissionlogs;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Mageplaza\Affiliate\Block\Account\Commissionlogs;
use Mageplaza\Affiliate\Model\Transaction\Type;

/**
 * Class Withdraw
 * @package Mageplaza\Affiliate\Block\Account
 */
class Commissions extends Commissionlogs
{
    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getTransactions()
    {
        $collection = $this->transactionFactory->create()
            ->getCollection()
            ->addFieldToFilter('account_id', $this->getCurrentAccount()->getId())
            ->addFieldToFilter('type', Type::COMMISSION);

        $collection->setOrder('transaction_id', 'DESC');

        if ($collection->getSize()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'affiliate.sales.pager');
            // assign collection to pager
            $pager->setAvailableLimit([10 => "10", 20 => "20", 50 => "50"])->setCollection($collection);
            $this->setChild('pager', $pager);// set pager block in layout
        }

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getCustomerOrdered($incrementId)
    {
        $order = $this->objectManager->create(Order::class)->loadByIncrementId($incrementId);

        if ($order) {
            return $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        }

        return '';
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function getStatus($id)
    {
        $customer = $this->customerFactory->create()->load($id);
        if ($customer) {
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        }

        return '';
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getCampaignName($id)
    {
        $campaign = $this->campaignFactory->create()->load($id);
        if ($campaign) {
            return $campaign->getName();
        }

        return '';
    }

    /**
     * @param $incrementId
     *
     * @return string
     */
    public function getOrder($incrementId, $idItem)
    {
        $totalSales = '';
        $order      = $this->objectManager->create(Order::class)->loadByIncrementId($incrementId);
        if ($order) {
            $item = ($order->getItemById($idItem));
            if ($item) {
                $totalSales = $this->formatPrice($item['price'] + $item['tax_amount'] - $item['discount_amount'] - $item['affiliate_discount_amount']);
            }
        }

        return $totalSales;
    }

    /**
     * @param $product_sku
     *
     * @return string
     */
    public function getProductName($product_sku)
    {
        $productName = '';
        if ($product_sku) {
                $product = $this->objectManager->create(Product::class)->loadByAttribute('sku', $product_sku);
                if ($product) {
                    $productName .= $product->getName();
                }
        };
        return $productName;
    }

}
