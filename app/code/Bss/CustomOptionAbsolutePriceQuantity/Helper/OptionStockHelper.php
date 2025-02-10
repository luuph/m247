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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Helper;

use Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\OptionQtyReport;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Helper\Context;

class OptionStockHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var OptionQtyReport
     */
    protected $optionQtyReport;

    /**
     * OptionStockHelper constructor.
     * @param Context $context
     * @param OptionQtyReport $optionQtyReport
     */
    public function __construct(
        Context $context,
        OptionQtyReport $optionQtyReport
    ) {
        $this->optionQtyReport = $optionQtyReport;
        parent::__construct($context);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function insertDataToStockManageTable($data)
    {
        $convertData = [];
        foreach ($data as $datum) {
            if (isset($datum[0])) {
                $convertData[] = $datum[0];
            } else {
                foreach ($datum as $item) {
                    $convertData[] = $item;
                }
            }
        }
        $this->optionQtyReport->createNewRow($convertData);
    }

    /**
     * @param mixed $order
     * @param null|mixed $creditmemo
     * @return array
     */
    public function getDataQtyOption($order, $creditmemo = null)
    {
        $data = [];
        $checkCredit = [];
        $creditItemQty = [];
        if ($creditmemo) {
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getProductType() == Type::TYPE_BUNDLE) {
                    continue;
                }
                $checkCredit[] = $item->getProductId();
                $creditItemQty[$item->getProductId()] = $item->getQty();
            }
        }
        foreach ($order->getAllItems() as $item) {
            if (!empty($checkCredit)) {
                if (!in_array($item->getProductId(), $checkCredit)) {
                    continue;
                }
            }
            if ($item->getProductType() == Type::TYPE_BUNDLE) {
                continue;
            }
            $optionData = $item->getProductOptions();
            if (isset($optionData['options'])) {
                $product = $item->getProduct();
                //set data from option
                $customData = $arrayObjects = [];
                $customData['optionData'] = $optionData;
                $customData['creditItemQty'] = $creditItemQty;
                $arrayObjects['order'] = $order;
                $arrayObjects['product'] = $product;
                $arrayObjects['creditmemo'] = $creditmemo;
                $this->setDataFromOption($arrayObjects, $customData, $item, $data);
            }
        }
        return $data;
    }

    /**
     * @param array $arrayObjects
     * @param array $customData
     * @param mixed $item
     * @param array $data
     */
    public function setDataFromOption($arrayObjects, $customData, $item, &$data)
    {
        $order = $arrayObjects['order'];
        $product = $arrayObjects['product'];
        $creditmemo = $arrayObjects['creditmemo'];
        $optionData = $customData['optionData'];
        $creditItemQty = $customData['creditItemQty'];
        foreach ($optionData['options'] as $option) {
            $optionQty = $this->setOptionQty($optionData, $option);
            $optionObj = $product->getOptionById($option['option_id']);
            if($optionObj) {
                $arrOptionData = [];
                $arrOptionData['optionQty'] = $optionQty;
                $arrOptionData['optionObj'] = $optionObj;
                $arrOptionData['option'] = $option;
                if (in_array($option['option_type'], TierPriceOptionHelper::SELECT_TYPE_OPTION)) {
                    //set option data from option value
                    $this->setOptionDataOfValue(
                        $arrayObjects,
                        $customData,
                        $arrOptionData,
                        $item,
                        $data
                    );
                } else {
                    if (isset($data[$option['option_id']][0])) {
                        $data[$option['option_id']][0]['qty'] +=
                            $optionQty * $optionData['info_buyRequest']['qty'];
                    } else {
                        $data[$option['option_id']][0] = [
                            'product_id'     => $product->getId(),
                            'product_name'   => $product->getName(),
                            'product_sku'    => $product->getSku(),
                            'option_title'   => $optionObj->getTitle(),
                            'option_value'   => null,
                            'option_type_id' => null,
                            'option_price'   => $this->getPriceByPriceType($product, $optionObj),
                            'qty'            => $optionQty * $optionData['info_buyRequest']['qty'],
                            'order_id'       => $order->getId(),
                            'creditmemo_id'  => null,
                            'created_at'     => $order->getCreatedAt()
                        ];
                    }
                    if ($creditmemo) {
                        $data[$option['option_id']][0]['creditmemo_id'] = $creditmemo->getId();
                        $data[$option['option_id']][0]['created_at'] = $creditmemo->getCreatedAt();
                        $data[$option['option_id']][0]['qty'] -=
                            $optionQty * $optionData['info_buyRequest']['qty'];
                        if (isset($creditItemQty[$item->getProductId()])) {
                            $data[$option['option_id']][0]['qty'] += $optionQty * $creditItemQty[$item->getProductId()];
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $arrayObjects
     * @param array $customData
     * @param array $arrOptionData
     * @param mixed $item
     * @param array $data
     */
    public function setOptionDataOfValue(
        $arrayObjects,
        $customData,
        $arrOptionData,
        $item,
        &$data
    ) {
        $order = $arrayObjects['order'];
        $product = $arrayObjects['product'];
        $creditmemo = $arrayObjects['creditmemo'];
        $optionData = $customData['optionData'];
        $creditItemQty = $customData['creditItemQty'];
        $optionQty = $arrOptionData['optionQty'];
        $optionObj = $arrOptionData['optionObj'];
        $option = $arrOptionData['option'];
        $optionValues = explode(",", $option['option_value'] ?? "");
        foreach ($optionValues as $optionValue) {
            if (isset($data[$option['option_id']][$optionValue])) {
                $data[$option['option_id']][$optionValue]['qty'] +=
                    $optionQty * $optionData['info_buyRequest']['qty'];
            } else {
                $opTionValue = $optionObj->getValueById($optionValue);
                $data[$option['option_id']][$optionValue] = [
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName(),
                    'product_sku' => $product->getSku(),
                    'option_title' => $optionObj->getTitle(),
                    'option_value' => $opTionValue->getTitle(),
                    'option_type_id' => $opTionValue->getOptionTypeId(),
                    'option_price' => $this->getPriceByPriceType($product, $opTionValue),
                    'qty' => $optionQty * $optionData['info_buyRequest']['qty'],
                    'order_id' => $order->getId(),
                    'creditmemo_id' => null,
                    'created_at' => $order->getCreatedAt()
                ];
            }
            if ($creditmemo) {
                $data[$option['option_id']][$optionValue]['creditmemo_id'] = $creditmemo->getId();
                $data[$option['option_id']][$optionValue]['created_at'] = $creditmemo->getCreatedAt();
                $data[$option['option_id']][$optionValue]['qty'] -=
                    $optionQty * $optionData['info_buyRequest']['qty'];
                if (isset($creditItemQty[$item->getProductId()])) {
                    $data[$option['option_id']][$optionValue]['qty'] += $optionQty * $creditItemQty[$item->getProductId()];
                }
            }
        }
    }

    /**
     * @param mixed $product
     * @param mixed $option
     * @return float|int
     */
    protected function getPriceByPriceType($product, $option)
    {
        if ($option->getPriceType() == 'percent') {
            return round(($product->getFinalPrice()/100) * $option->getPrice(), 2);
        }
        return round($option->getPrice(), 2);
    }

    /**
     * @param array $optionData
     * @param array $option
     * @return int|mixed
     */
    public function setOptionQty($optionData, $option)
    {
        $optionQty = 1;
        if (isset($optionData['info_buyRequest']['option_qty'][$option['option_id']])) {
            $optionQty = $optionData['info_buyRequest']['option_qty'][$option['option_id']];
        }
        if (isset($option['option_qty'])) {
            $optionQty = $option['option_qty'];
        }
        return $optionQty;
    }
}
