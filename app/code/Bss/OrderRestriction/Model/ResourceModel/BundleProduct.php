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
 * @package    Bss_OrderRestriction
 * @author     Extension Team
 * @copyright  Copyright (c) 2021-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

/**
 * Class BundleProduct
 * Get default selection value
 */
class BundleProduct extends AbstractDb
{

    /**
     * Fetch the selection default value
     *
     * @param int $selectionId
     * @return array|false
     */
    public function getSelectionDefaultValue($selectionId)
    {
        try {
            $connection = $this->resource->getConnection();

            $select = $connection->select();

            $select->from(
                ['bundle_selection' => $this->getTable("catalog_product_bundle_selection")],
                []
            )->where('selection_id = ?', $selectionId);

            $select->columns([
                'product_id',
                'qty' => 'selection_qty'
            ]);

            return $connection->fetchRow($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
