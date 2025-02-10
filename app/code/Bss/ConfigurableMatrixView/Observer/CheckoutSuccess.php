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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type;

/**
 * Clear product cache after success checkout
 */
class CheckoutSuccess implements ObserverInterface
{
    /**
     * @var \Bss\ConfigurableMatrixView\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var Type
     */
    protected $fullPageCache;

    /**
     * CheckoutSuccess constructor.
     *
     * @param \Bss\ConfigurableMatrixView\Helper\Data $helper
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param Type $fullPageCache
     */
    public function __construct(
        \Bss\ConfigurableMatrixView\Helper\Data $helper,
        \Magento\Framework\App\CacheInterface $cache,
        Type $fullPageCache
    ) {
        $this->helper = $helper;
        $this->cache = $cache;
        $this->fullPageCache = $fullPageCache;
    }

    /**
     * Execute flush cache
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnabled()
            && $this->helper->canShowStock()
            && $this->helper->canFlushConfigurableProduct()
        ) {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $observer->getOrder();
            foreach ($order->getItems() as $item) {
                if ($item->getProductType() == 'configurable') {
                    $tag = \Magento\Catalog\Model\Product::CACHE_TAG . "_" . $item->getProductId();
                    $tags = [$tag];
                    $this->fullPageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
                    $this->cache->clean($tags);
                }
            }
        }
    }
}
