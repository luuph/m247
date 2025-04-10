<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Walletsystem Class
 */
class WebsiteSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepo;
    
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    protected $websiteRepo;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Request\Http           $request
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param ProductRepositoryInterface                    $productRepo
     * @param \Magento\Store\Model\WebsiteRepository        $websiteRepo
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepo,
        \Magento\Store\Model\WebsiteRepository $websiteRepo
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->productRepo = $productRepo;
        $this->websiteRepo = $websiteRepo;
    }
    
    /**
     * Add website id to wallet amount product.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paramData = $this->request->getParams();
        $website = $this->websiteRepo->get($paramData['website']['code']);
        if (!empty($paramData['store_type']) || !empty($paramData['store_action'])) {
            if ($paramData['store_type'] == 'website' && !empty($website)) {
                if ($website->getWebsiteId()) {
                    $websiteId = $website->getWebsiteId();
                    $product = $this->productRepo->get(\Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU);
                    $websiteIds = $product->getWebsiteIds();
                    if (!in_array($websiteId, $websiteIds)) {
                        $websiteIds[] = $websiteId;
                        $product->setWebsiteIds($websiteIds);
                        $product->save();
                    }
                }
            }
        }
    }
}
