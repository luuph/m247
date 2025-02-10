<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Block\Hyva\Creditmemo;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Mageplaza\PdfInvoice\Block\CustomPrint;
use Mageplaza\PdfInvoice\Helper\Data;
use Mageplaza\PdfInvoice\Model\Source\Type;

/**
 * Class Items
 * @package Mageplaza\PdfInvoice\Block\Hyva\Invoice
 */
class Items extends \Magento\Sales\Block\Order\Creditmemo\Items
{
    /**
     * @var CustomPrint
     */
    protected $helperConfig;

    public function __construct(
        Data $helperConfig,
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->helperConfig = $helperConfig;

        parent::__construct($context, $registry, $data);
    }

    /**
     * @param $invoice
     * @return array
     */
    public function getPrintBtnInfo($creditmemo) {
        $param = [
            'order_id'      => $this->getOrder()->getId(),
            'creditmemo_id' => $creditmemo->getId()
        ];
        $url             = $this->getUrl('pdfinvoice/' . Type::CREDIT_MEMO . '/print', $param);
        $showCustomPrint = $this->helperConfig->canShowCustomPrint(Type::CREDIT_MEMO, $creditmemo->getStoreId(),true);
        $label           = $this->helperConfig->getLabel(Type::CREDIT_MEMO, $creditmemo->getStoreId());

        return [$url, $showCustomPrint, $label];
    }
}
