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

namespace Webkul\Walletsystem\Plugin\Backend\Block\Widget;

/**
 * Webkul Walletsystem Class
 */
class Container
{
    public const  IS_WALLET_SYSTEM = "+ Webkul Wallet System";

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Initialize dependency
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }

    /**
     * Before plugin for addbutton function to change Invoice Button Label
     *
     * @param \Magento\Backend\Block\Widget\Container $subject
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return $this
     */
    public function beforeAddButton(
        \Magento\Backend\Block\Widget\Container $subject,
        $buttonId,
        $data,
        $level = 0,
        $sortOrder = 0,
        $region = 'toolbar'
    ) {
        $order = $this->getOrder();
        if (!$order || $buttonId != "order_invoice") {
            return [$buttonId,$data,$level,$sortOrder,$region];
        }
        $methodTitle = $this->getPaymentTitle($order);
        //to remove edit button when payment method is wallet system and the other
        if (strpos($methodTitle, self::IS_WALLET_SYSTEM) !== false) {
                $subject->removeButton('order_edit');
        }
        try {
            if ($order->getItemsCollection()->getFirstItem()
            ->getSku() == \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU) {
                $label = __("Confirm Wallet Order");
                $data['label'] = $label;
            }
        } catch (\Exception $e) {
            return [$buttonId,$data,$level,$sortOrder,$region];
        }
        return [$buttonId,$data,$level,$sortOrder,$region];
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * Get Payment Title
     *
     * @param object $order
     * @return string
     */
    public function getPaymentTitle($order)
    {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        return $method->getTitle();
    }
}
