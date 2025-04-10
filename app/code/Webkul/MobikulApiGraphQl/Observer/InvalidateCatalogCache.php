<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApiGraphQl\Observer;

/**
 * InvalidateCatalogCache Class observer
 */
class InvalidateCatalogCache implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Cache variable
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * Execute Function
     *
     * @param \Magento\Framework\Event\Observer $observer $observer
     *
     * @return void|json
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cache = $this->getCache();
        $tags = ["mobikul_product_cache"];
        $cache->clean($tags);
    }

    /**
     * GetCache function
     *
     * @return void
     */
    public function getCache()
    {
        if (empty($this->cache)) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->cache =  $om->get(\Magento\Framework\App\CacheInterface::class);
        }
        return $this->cache;
    }
}
