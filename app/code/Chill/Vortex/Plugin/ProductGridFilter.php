<?php
namespace Chill\Vortex\Plugin;

use Magento\Framework\Data\Collection;

class ProductGridFilter
{
    public function beforeSetCollection(
        \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid $subject,
        Collection $collection
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        
        $sku = $request->getParam('sku');
        $productId = $request->getParam('product_id');

        if ($sku) {
            $collection->addFieldToFilter('sku', ['like' => "%$sku%"]);
        }

        if ($productId) {
            $collection->addFieldToFilter('entity_id', $productId);
        }

        return [$collection];
    }
}
