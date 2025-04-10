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
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Observer;

use Bss\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderExtensionInterfaceFactory;
use Magento\Sales\Model\Order;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var OrderExtensionInterfaceFactory
     */
    private $orderExtensionFactory;

    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Construct
     *
     * @param OrderExtensionInterfaceFactory $orderExtensionFactory
     * @param GiftCardHelper $giftCardHelper
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        OrderExtensionInterfaceFactory $orderExtensionFactory,
        GiftCardHelper $giftCardHelper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\State $state
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->giftCardHelper = $giftCardHelper;
        $this->url = $url;
        $this->state = $state;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }

        $giftCardAmount = $order->getData('bss_giftcard_amount');
        $storeId = $order->getStoreId();

        if ($this->state->getAreaCode() == "graphql") {
            $this->checkAndSetInfoBuyRequestGiftCardProduct($order->getAllItems());
        }

        if ($this->giftCardHelper->isEnabled($storeId)
            && $giftCardAmount
            && $giftCardAmount > 0
        ) {
            $extensionAttributes->setGiftCardAmount($giftCardAmount);
            $order->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * Check and set buy request of gift card
     *
     * @param $listItem
     * @return void
     */
    public function checkAndSetInfoBuyRequestGiftCardProduct($listItem)
    {
        foreach ($listItem as $item) {
            $productOptions = $item->getData('product_options');
            if ($item->getData("product_type")
                && $productOptions
                && isset($productOptions['info_buyRequest'])
                && isset($productOptions['info_buyRequest']['giftcard_options'])
            ) {
                $urlProduct = $this->url->getUrl('catalog/product/view', ['id' => $item->getProductId()]);
                $productOptions['info_buyRequest']['uenc'] = base64_encode($urlProduct);
                $giftCardOptions = $productOptions['info_buyRequest']['giftcard_options'];
                if (isset($productOptions['info_buyRequest']['options']) && count($productOptions['info_buyRequest']['options']) > 0) {
                    $productOptionArray = $productOptions['info_buyRequest']['options'];
                    unset($productOptions['info_buyRequest']['giftcard_options']);
                    $productOptions['info_buyRequest'] = array_merge($productOptions['info_buyRequest'], $productOptionArray);
                }
                $productOptions['info_buyRequest'] = array_merge($productOptions['info_buyRequest'], $giftCardOptions);
                $item->setData('product_options', $productOptions);
                $item->save();
            }
        }
    }
}
