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
 * @package    Bss_ProductGridInlineEditor
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductGridInlineEditor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Product grid inline class Quantity
 *
 */
class SourceQuantity extends Column
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $stockItemConfiguration;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $allowedForProductType;

    /**
     * constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetStockItemConfigurationInterface $stockItemConfiguration
     * @param IsSourceItemManagementAllowedForProductTypeInterface $allowedForProductType
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DefaultStockProviderInterface $defaultStockProvider,
        GetStockItemConfigurationInterface $stockItemConfiguration,
        IsSourceItemManagementAllowedForProductTypeInterface $allowedForProductType,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->defaultStockProvider = $defaultStockProvider;
        $this->stockItemConfiguration = $stockItemConfiguration;
        $this->allowedForProductType = $allowedForProductType;
    }

    /**
     * Validate Stock Items Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['valid'] = false;

                if (isset($item['sku'], $item['type_id'])) {
                    $item['valid'] = $this->isItemValid($item['sku'], $item['type_id']);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Validate
     *
     * @param string $sku
     * @param string $typeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    private function isItemValid($sku, $typeId): bool
    {
        $isValid = false;
        if ($this->allowedForProductType->execute($typeId) === true) {
            $isValid = $this->stockItemConfiguration->execute($sku, $this->defaultStockProvider->getId())
                ->isManageStock();
        }

        return $isValid;
    }
}
