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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Plugin\Block\Widget\Grid;

class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    /**
     * Set input element name
     *
     * @return \Magento\Backend\Block\Widget\Grid\Serializer|void
     */
    public function _beforeToHtml()
    {
        if ($this->getRequest()->getParam('products_related')) {
            $this->setInputElementName("bss_input");
        }
        parent::_beforeToHtml();
    }
}
