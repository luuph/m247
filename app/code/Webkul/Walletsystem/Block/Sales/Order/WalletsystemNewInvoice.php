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

namespace Webkul\Walletsystem\Block\Sales\Order;

use Magento\Sales\Model\Order;
use Webkul\Walletsystem\Helper\Data;

/**
 * Webkul Walletsystem Class
 */
class WalletsystemNewInvoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data                                             $walletHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $walletHelper,
        array $data = []
    ) {
        $this->walletHelper = $walletHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model.
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Display full summary
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Initialize all order totals.
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $invoice = $parent->getInvoice();
        $this->order = $invoice->getOrder();
        $title = __('Wallet Amount');
        $walletamount = 0;
        $invoiceCollection = $this->order->getInvoiceCollection();
        foreach ($invoiceCollection as $previousInvoice) {
            if ($previousInvoice->getEntityId()) {
                if (!$previousInvoice->isCanceled()) {
                    $walletamount = $walletamount + $previousInvoice->getWalletAmount();
                }
            }
        }
        if ($walletamount != $this->order->getWalletAmount()) {
            $amount = $this->order->getWalletAmount() - $walletamount;
            // calculate base currency amount
            $baseCurrency = $this->walletHelper->getBaseCurrencyCode();
            $orderCurrency = $this->order->getOrderCurrencyCode();
            $baseAmount = $this->walletHelper->getwkconvertCurrency($orderCurrency, $baseCurrency, $amount);
            
            $walletPayment = new \Magento\Framework\DataObject(
                [
                    'code'          => 'wallet_amount',
                    'strong'        => false,
                    'value'         => $amount,
                    'base_value'    => $baseAmount,
                    'label'         => __($title),
                ]
            );
            $parent->addTotal($walletPayment, 'wallet_amount');
        }

        return $this;
    }

    /**
     * Get order store object.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->order->getStore();
    }

    /**
     * Get Order function
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get Label Properties function
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get Value Properties function
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
