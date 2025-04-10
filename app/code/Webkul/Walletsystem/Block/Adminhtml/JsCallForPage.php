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
 * Webkul Walletsystem Js Call For Page Block
 */
class JsCallForPage extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $_template = 'Webkul_Walletsystem::js.phtml';

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Json\Helper\Data $helper,
        array $data = []
    ) {
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if user has wallet
     *
     * @return int
     */
    public function getUserHasWallet()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($orderId);
        return $order->getCustomerId();
    }

    /**
     * Get JSON helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
