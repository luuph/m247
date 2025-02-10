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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Product Data
 *
 * Bss\RewardPoint\Helper
 */
class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var ProductRepository
     */
    protected $productInfo;

    /**
     * @var StockRegistry
     */
    protected $stockRegistry;

    /**
     * @var Configurable
     */
    protected $configurableData;

    /**
     * @var RewardCustomAction
     */
    protected $customActionHelper;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductData constructor.
     * @param Context $context
     * @param ProductRepository $productInfo
     * @param StockRegistry $stockRegistry
     * @param Configurable $configurableData
     * @param RewardCustomAction $customActionHelper
     * @param SessionFactory $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ProductRepository $productInfo,
        StockRegistry $stockRegistry,
        Configurable $configurableData,
        RewardCustomAction $customActionHelper,
        SessionFactory $customerSession,
        StoreManagerInterface $storeManager
    ) {
        $this->productInfo = $productInfo;
        $this->stockRegistry = $stockRegistry;
        $this->configurableData = $configurableData;
        $this->customActionHelper = $customActionHelper;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get child data of configurable product
     *
     * @param int $productEntityId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        $map_r = [];
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        $product = $this->productInfo->getById($productEntityId);

        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;
        foreach ($parentAttribute as $attrKey => $attrValue) {
            foreach ($product->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                ->getOptions() as $tvalue) {
                $result['map'][$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $result['map'][$attrValue->getAttributeId()][$tvalue->getValue()] = $tvalue->getLabel();
                $map_r[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
            }
        }

        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productInfo->getById($childProduct['entity']);
            $childStock = $this->stockRegistry->getStockItem($childProduct['entity']);
            $childProduct['stock_number'] = $childStock->getQty();
            $childProduct['sku'] = $child->getSku();
            $customerGroupId = $this->customerSession->create()->getCustomer()->getGroupId();
            $point = $this->customActionHelper->getPointByProduct($child, $customerGroupId);
            $childProduct['reward_point'] = '';
            $dependQty = $child->getResource()->getAttributeRawValue(
                $child->getId(),
                'dependent_qty',
                $this->storeManager->getStore()->getId()
            );
            if ($point > 0) {
                $childProduct['reward_point'] = $this->getMessaseByCustomerGroup(
                    $dependQty,
                    $customerGroupId,
                    $point
                );
            }

            $key = '';
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                $key .= $map_r[$attrValue->getAttributeId()][$childRow] . '_';
            }
            $result['child'][$key] = $childProduct;
        }
        return $result;
    }

    /**
     * Get reward point message
     *
     * @param int $dependQty
     * @param int $customerGroupId
     * @param int $point
     * @return \Magento\Framework\Phrase
     */
    protected function getMessaseByCustomerGroup($dependQty, $customerGroupId, $point)
    {
        if ($customerGroupId > 0) {
            if ($dependQty) {
                return __("Earn %1 points for 1 product item", $point);
            }
            return __("Buy this product to earn %1 points", $point);
        } else {
            if ($dependQty) {
                return __("Login and earn %1 points for 1 product item", $point);
            }
            return __("Login and Buy this product to earn %1 points", $point);
        }
    }
}
