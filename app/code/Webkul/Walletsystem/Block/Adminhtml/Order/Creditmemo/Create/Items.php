<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Adminhtml\Order\Creditmemo\Create;

/**
 * Webkul Walletsystem Prefernce class to add refund button for wallet
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items
{
    /**
     * Helper data
     *
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        \Webkul\Walletsystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $salesData,
            $data
        );
    }

    /**
     * Prepare child blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $paymentMethod = $this->getOrder()->getPayment()->getMethod();
        if ($this->helper->getWalletenabled() && $paymentMethod != "walletsystem") {
            $this->addChild(
                'submit_after',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Refund In Wallet'),
                    'id' => "wk-wallet-refund-button",
                    'class' => 'save submit-button primary',
                    'onclick' => ''
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
