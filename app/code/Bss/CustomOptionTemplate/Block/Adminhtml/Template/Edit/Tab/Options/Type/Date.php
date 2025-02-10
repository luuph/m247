<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Block\Adminhtml\Template\Edit\Tab\Options\Type;

class Date extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Date
{
    /**
     * @var string
     */
    protected $_template = 'Bss_CustomOptionTemplate::catalog/product/edit/options/type/date.phtml';

    /**
     * @var \Bss\CustomOptionTemplate\Helper\Data
     */
    protected $helper;

    /**
     * Date constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice
     * @param \Bss\CustomOptionTemplate\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
        \Bss\CustomOptionTemplate\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $optionPrice, $data);
    }

    /**
     * @return bool
     */
    public function isCompatibleAbs()
    {
        return $this->helper->isCompatibleAbsolutePriceQuantity();
    }
}
