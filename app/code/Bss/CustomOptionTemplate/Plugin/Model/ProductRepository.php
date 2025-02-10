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
namespace Bss\CustomOptionTemplate\Plugin\Model;

use Bss\CustomOptionTemplate\Model\AssignTemplateToProduct;
use Magento\Catalog\Api\Data\ProductInterface;

class ProductRepository
{

    /**
     * @var AssignTemplateToProduct
     */
    private AssignTemplateToProduct $assignTemplateToProduct;

    /**
     * @param AssignTemplateToProduct $assignTemplateToProduct
     */
    public function __construct(
        AssignTemplateToProduct $assignTemplateToProduct
    ) {
        $this->assignTemplateToProduct = $assignTemplateToProduct;
    }

    /**
     * Set Template after save product (Create/Update)
     *
     * @param \Magento\Catalog\Model\ProductRepository $subject
     * @param ProductInterface $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave($subject, $result)
    {
        $this->assignTemplateToProduct->setTemplateToProduct($result);
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\ProductRepository $subject
     * @param $result
     * @param ProductInterface $product
     */
    public function afterDelete($subject, $result, ProductInterface $product)
    {
        $this->assignTemplateToProduct->unsetTemplateToProduct($product->getId());
        return $result;
    }
}
