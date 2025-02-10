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

namespace Mageplaza\PdfInvoice\Block\Hyva\Shipment;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Mageplaza\PdfInvoice\Block\CustomPrint;
use Mageplaza\PdfInvoice\Helper\Data;
use Mageplaza\PdfInvoice\Model\Source\Type;

/**
 * Class Items
 * @package Mageplaza\PdfInvoice\Block\Hyva\Shipment
 */
class Items extends \Magento\Sales\Block\Order\Items
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
     * @param $shipment
     * @return array
     */
    public function getPrintBtnInfo($shipment) {
        $param = [
            'order_id'    => $this->getOrder()->getId(),
            'shipment_id' => $shipment->getId()
        ];
        $url             = $this->getUrl('pdfinvoice/' . Type::SHIPMENT . '/print', $param);
        $showCustomPrint = $this->helperConfig->canShowCustomPrint(Type::SHIPMENT, $shipment->getStoreId(),true);
        $label           = $this->helperConfig->getLabel(Type::SHIPMENT, $shipment->getStoreId());

        return [$url, $showCustomPrint, $label];
    }
}
