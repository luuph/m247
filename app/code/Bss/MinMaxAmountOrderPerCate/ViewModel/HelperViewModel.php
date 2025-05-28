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
 * @package    Bss_MinMaxAmountOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxAmountOrderPerCate\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class HelperViewModel extends DataObject implements ArgumentInterface
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $dataHelper;

    /**
     * HelperViewModel constructor.
     * @param \Magento\Msrp\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Msrp\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param int|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowBeforeOrderConfirm($product)
    {
        return $this->dataHelper->isShowBeforeOrderConfirm($product);
    }

    /**
     * @param int|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMinimalPriceLessMsrp($product)
    {
        return $this->dataHelper->isMinimalPriceLessMsrp($product);
    }
}
