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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\OptionQtyReport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(
            \Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReport::class,
            \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\OptionQtyReport::class
        );
    }

    /**
     * @return AbstractCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if ($this->getConnection()->isTableExists($this->getTable('bss_catalog_product_option_type_image'))) {
            $this->getSelect()->joinLeft(
                ['secondTable' => $this->getTable('bss_catalog_product_option_type_image')],
                'main_table.option_type_id = secondTable.option_type_id',
                ['image_url']
            );
        }
    }
}
