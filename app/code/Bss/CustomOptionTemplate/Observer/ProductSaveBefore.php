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
namespace Bss\CustomOptionTemplate\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Template
     */
    protected $modelResourceTemplate;

    /**
     * ProductSaveBefore constructor.
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\Template $modelResourceTemplate
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\ResourceModel\Template $modelResourceTemplate
    ) {
        $this->modelResourceTemplate = $modelResourceTemplate;
    }

    /**
     * Catalog Product Before Save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        //get data of attribute product by attribute code
        $excludeTemplate = $this->modelResourceTemplate->getAttribeProductData(
            $productId,
            'tenplates_excluded'
        );
        $oldExcludeTemplate = $newExcludeTemplate = [];
        if ($excludeTemplate) {
            $oldExcludeTemplate = explode(",", $excludeTemplate);
        }
        if ($product->getData('tenplates_excluded')) {
            if (is_array($product->getData('tenplates_excluded'))) {
                $newExcludeTemplate = explode(",", $product->getData('tenplates_excluded')[0]);
            } else {
                $newExcludeTemplate = explode(",", $product->getData('tenplates_excluded'));
            }
        }
        $addNewTemplate = array_diff($oldExcludeTemplate, $newExcludeTemplate);
        //event catalog_product_save__after will get data from this
        $product->setData('add_new_template_from_exclude', $addNewTemplate);
    }
}
