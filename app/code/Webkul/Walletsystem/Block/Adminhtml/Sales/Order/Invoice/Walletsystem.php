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

/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Webkul\Walletsystem\Block\Adminhtml\Sales\Order\Invoice;

class Walletsystem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Display Summary
     *
     * @return boolean
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Source getter function
     *
     * @return object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Store getter function
     *
     * @return object
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Order getter function
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Returns label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Returns value
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $store = $this->getStore();
        $title = 'Wallet Amount';
        $walletAmount = new \Magento\Framework\DataObject(
            [
                    'code' => 'walletsystem',
                    'strong' => false,
                    'value' => $this->_order->getWalletAmount(),
                    'base_value' => $this->_order->getBaseWalletAmount(),
                    'label' => __($title),
                ]
        );
       
        $parent->addTotal($walletAmount, 'walletamount');
        
        return $this;
    }
}
