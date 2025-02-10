<?php

namespace Bss\MultiWishlist\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;

class ConfigChange implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    private $resourceConnection;

    public function __construct(RequestInterface $request, ResourceConnection $resourceConnection)
    {
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        $params = $this->request->getParams();
        $isEnabled = (bool)(int)$params['groups']['general']['fields']['enable']['value'];

        if (!$isEnabled) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('wishlist_item');

            $connection->delete($table, ['multi_wishlist_id' => ['neq' => 0]]);
        }
    }
}
