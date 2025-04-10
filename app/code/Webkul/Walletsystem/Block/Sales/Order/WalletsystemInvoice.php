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
class WalletsystemInvoice extends \Magento\Framework\View\Element\Template
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
     * @param Data $walletHelper
     * @param array $data
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
     * Get source
     *
     * @return source
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
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        $title = __('Wallet Amount');
        $store = $this->getStore();
        $invoiceCollection = $this->order->getInvoiceCollection();
        $baseCurrency = $this->walletHelper->getBaseCurrencyCode();
        $orderCurrency = $this->order->getOrderCurrencyCode();
        foreach ($invoiceCollection as $previousInvoice) {
            $walletamount = $previousInvoice->getWalletAmount();
            if ((double) $walletamount && !$previousInvoice->isCanceled()) {
                if ($invoice->getId() == $previousInvoice->getId()) {
                    $invoiceAmount = $invoice->getWalletAmount();
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $orderCurrency,
                        $baseCurrency,
                        $invoiceAmount
                    );
                    $walletPayment = new \Magento\Framework\DataObject(
                        [
                            'code' => 'wallet_amount',
                            'strong' => false,
                            'value' => $invoiceAmount,
                            'base_value' => $baseAmount,
                            'label' => __($title),
                        ]
                    );
                    $parent->addTotal($walletPayment, 'wallet_amount');
                    break;
                }
            }
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

    /**
     * Get helper
     *
     * @return object
     */
    public function getWkHelper()
    {
        return $this->walletHelper;
    }
}
