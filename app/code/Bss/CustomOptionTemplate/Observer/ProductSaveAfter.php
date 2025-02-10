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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Observer;

use Bss\CustomOptionTemplate\Model\AssignTemplateToProduct;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var AssignTemplateToProduct
     */
    protected $assignTemplateToProduct;

    /**
     * @param AssignTemplateToProduct $assignTemplateToProduct
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\AssignTemplateToProduct $assignTemplateToProduct
    ) {
        $this->assignTemplateToProduct = $assignTemplateToProduct;
    }

    /**
     * Catalog Product After Save (Add or remove template assign for product)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->assignTemplateToProduct->setTemplateToProduct($product);
    }
}
