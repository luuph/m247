<?php

namespace Bss\ConfiguableGridView\Model\MultiInventorySource;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class MsiObjectManagement
{
    /**
     * Const
     */
    const MSI_MODULE_CORE = 'Magento_Inventory';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManagerFactory;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var null
     */
    protected $getStockItemDataInterface;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManagerFactory
     * @param Manager $moduleManager
     * @param $getStockItemDataInterface
     */
    public function __construct(
        ObjectManagerInterface $objectManagerFactory,
        Manager $moduleManager,
        $getStockItemDataInterface = null
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->moduleManager = $moduleManager;
        $this->getStockItemDataInterface = $getStockItemDataInterface;
    }

    /**
     * @return bool
     */
    public function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled(self::MSI_MODULE_CORE);
    }

    /**
     * @param $objectName
     * @param array $data
     * @return object|null
     */
    public function getObjectInstance($objectName, $data = [])
    {
        if ($this->isMsiEnabled()) {
            return $this->objectManagerFactory->create(
                $objectName,
                $data
            );
        }
        return null;
    }

    /**
     * @param array $data
     * @return object|null
     */
    public function getStockItemDataInterface($data = [])
    {
        $stockItemDataInterface =  $this->getObjectInstance(
            $this->getStockItemDataInterface,
            $data
        );

        if ($stockItemDataInterface instanceof \Magento\InventorySalesApi\Model\GetStockItemDataInterface) {
            return $stockItemDataInterface;
        }
        return null;
    }
}
