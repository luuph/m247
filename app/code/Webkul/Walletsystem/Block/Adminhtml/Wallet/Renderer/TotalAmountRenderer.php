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

namespace Webkul\Walletsystem\Block\Adminhtml\Wallet\Renderer;

/**
 * Webkul Walletsystem Class
 */
class TotalAmountRenderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Initialize dependencies
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Return Actions.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $currency = $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        $totalAmount = !empty($row->getTotalAmount())?$row->getTotalAmount():"0.0000";
        return $currency.$totalAmount;
    }
}
