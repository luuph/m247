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
define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry'
], function (ModalComponent, registry) {
    'use strict';

    return ModalComponent.extend({
        closeModal: function () {
            var modal = registry.get('product_listing.product_listing.bss_source_listing_modal');
            if (modal.needReload) {
                var productGrid = registry.get("product_listing.product_listing_data_source");
                productGrid.params.random = Math.random();
                productGrid.reload();
            }
            registry.get('bss_inventory_source_listing.bss_inventory_source_listing.columns.ids').deselectAll();
            this._super();
        }
    });
});
