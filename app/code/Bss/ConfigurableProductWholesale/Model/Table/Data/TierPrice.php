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
 * @package    Bss_ConfigurableProductWholesale
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */


namespace Bss\ConfigurableProductWholesale\Model\Table\Data;

use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Bss\ConfigurableProductWholesale\Model\DataInterface;
use Magento\Store\Model\StoreManagerInterface;

class TierPrice implements DataInterface
{
    const MODEL_NAME = 'tierPrice';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WholesaleData
     */
    private $helper;

    /**
     * TierPrice constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param WholesaleData $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        WholesaleData $helper
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return self::MODEL_NAME;
    }

    /**
     * @param $productCollection
     * @return object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareCollection($productCollection)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $productCollection->addAttributeToSelect('*')->addStoreFilter($storeId);
    }

    /**
     * @param $productCollection
     * @return array
     */
    public function getData($productCollection)
    {
        $data = [];
        foreach ($productCollection as $product) {
            $this->helper->getEventManager()->dispatch('bss_cpd_prepare_product_tier_price', ['product' => $product]);
            $productId = $product->getId();
            $tierPriceModel = $product->getPriceInfo()->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            if (isset($tierPricesList) && !empty($tierPricesList)) {
                foreach ($tierPricesList as $price) {
                    $data[$productId]['tierPrice'][] = [
                        'qty' => $price['price_qty'],
                        'price' => $price['price']->getValue(),
                        'price_excl_tax' => $price['price']->getValue(['tax']),
                        'save_percent' => $tierPriceModel->getSavePercent($price['price'])
                    ];
                }
            }
        }
        return $data;
    }
}
