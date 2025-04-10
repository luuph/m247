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

/**
 * Webkul Walletsystem Class
 */
class Walletsystem extends \Magento\Framework\View\Element\Template
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
     * Get source
     *
     * @return object
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
     * Add wallet amount in totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        $title = 'Wallet Amount';
        $store = $this->getStore();
        if ($this->order->getWalletAmount() != 0) {
            $walletAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'walletamount',
                    'strong' => false,
                    'value' => $this->order->getWalletAmount(),
                    'base_value' => $this->order->getBaseWalletAmount(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($walletAmount, 'walletamount');
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
