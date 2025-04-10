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

namespace Webkul\Walletsystem\Plugin;

/**
 * Webkul Walletsystem Class
 */
class Sales
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * Initialize dependencies
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->order = $order;
        $this->request = $request;
    }

    /**
     * After Can Credit Memo
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param object $result
     * @return object
     */
    public function afterCanCreditmemo(
        \Magento\Sales\Model\Order $subject,
        $result
    ) {
        $orderId = $this->request->getParam('order_id');
        $order = $this->order->load($orderId);
        if ($order->getItemsCollection()->getFirstItem()
        ->getSku() == \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU) {
            return false;
        }
        return $result;
    }
}
