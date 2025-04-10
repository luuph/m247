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

namespace Webkul\Walletsystem\Block\Adminhtml;

/**
 * Webkul Walletsystem Method Column Block
 */
class MethodColumn extends \Magento\Backend\Block\Widget\Grid\Column
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $order;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Payment\Model\PaymentMethodListFactory
     */
    protected $paymentModel;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\PaymentMethodListFactory $paymentModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\PaymentMethodListFactory $paymentModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
        $this->storeManager = $storeManager;
        $this->paymentModel = $paymentModel;
    }

    /**
     * Retrieve row column field value for display
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowField(\Magento\Framework\DataObject $row)
    {
        if ($row->getOrderId() != "") {
            $orderId = $row->getOrderId();
            $salesOrder = $this->order->create();
            $order = $salesOrder->load($orderId);
            if (ceil($order->getWalletAmount())) {
                if ($row->getMethod() != "walletsystem") {
                    //get list of payment methods
                    $paymentMethodsList = $this->paymentModel->create()->getActiveList(
                        $this->storeManager->getStore()->getId()
                    );
                    foreach ($paymentMethodsList as $method) {
                        if ($method->getCode() == $row->getMethod()) {
                            return $method->getTitle()." + Webkul Wallet System";
                        }
                    }
                }
            }
        }
        return parent::getRowField($row);
    }
}
