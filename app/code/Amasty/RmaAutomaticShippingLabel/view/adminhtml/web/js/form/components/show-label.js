define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'uiLayout',
    'mageUtils',
    'mage/translate'
], function ($, Modal, layout, utils, $t) {
    'use strict';

    return Modal.extend({
        defaults: {
            customsValue: 'Customs Value'
        },

        initializeLabel: function (data) {
            var item;

            this.prepareParams(data);
            item = this.createItem(data, 1);
            layout([ item ]);
            this.insertChild(item.name);
        },

        createItem: function (item, index) {
            return utils.extend(item, {
                'name': 'carrier-' + index,
                'component': 'Magento_Ui/js/form/components/fieldset',
                'template': 'Amasty_RmaAutomaticShippingLabel/form/show-label',
                'itemsTmpl': 'Amasty_RmaAutomaticShippingLabel/form/label/items_grid',
                'packageTmpl': 'Amasty_RmaAutomaticShippingLabel/form/label/package'
            });
        },

        prepareParams: function (data) {
            $.each(data.packages, function (index, item) {
                var params = [],
                    key;

                for (key in item.params) {
                    var data = {};

                    data.label = item.params[key];
                    data.key = key;
                    params.push(data);
                }

                item.isHideCustomsValue = this.getIsHideCustomsValue(item.packageLabels);
                item.params = params;
            }.bind(this));
        },

        getPackageLabel: function (index) {
            return $t('Package') + ' ' + (index + 1);
        },

        getIsHideCustomsValue: function (data) {
            var isHideCustomsValue = false;

            data.forEach(function (item) {
                if (item.label === 'Customs Value') {
                    isHideCustomsValue = item.isHidden;
                }
            });

            return isHideCustomsValue;
        }
    });
});
