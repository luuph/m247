define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'uiLayout',
    'mageUtils'
], function ($, Modal, registry, layout, utils) {
    'use strict';

    return Modal.extend({
        defaults: {
            elemIndex: 0,
            carriersUrl: '',
            packagesUrl: '',
            request_id: '',
            buttonSelector: '.amshipping-apply-button',
            createPackagesName: 'amrma_request_form.amrma_request_form.create-packages',
            shippingInformation: [],
            packagesData: [],
            css: {
                disabled: 'disabled'
            }
        },

        initObservable: function () {
            this._super().observe([
                'shippingInformation',
                'packagesData'
            ]);

            return this;
        },

        initCarriers: function (carriers) {
            var item,
                key;

            for (key in carriers) {
                item = this.createItem(carriers[key], this.elemIndex);
                layout([ item ]);
                this.insertChild(item.name);
                this.elemIndex += 1;
            }
        },

        showApplyButton: function (method, carrier) {
            this.getShippingInformation(method(), carrier);
            $(this.buttonSelector).removeClass(this.css.disabled);
        },

        applyPackage: function () {
            this.closeModal();

            registry.async(this.createPackagesName)(function (modal) {
                this.getPackages.call(this, modal);
            }.bind(this));
        },

        getPackages: function (modal) {
            var self = this;

            $.ajax({
                url: this.packagesUrl,
                data: {
                    request_id: this.request_id,
                    method_code: this.shippingInformation().code
                },
                showLoader: true,
                method: 'get',
                dataType: 'json',
                success: function (res) {
                    self.packagesData(JSON.parse(res));
                    modal.openModal();
                }
            });
        },

        getShippingInformation: function (method, carrier) {
            method.carrier_title = carrier.title;
            method.method_title = method.title;

            this.shippingInformation(method);
        },

        createItem: function (item, index) {
            return utils.extend(item, {
                'name': 'carrier-' + index,
                'component': 'Magento_Ui/js/form/components/fieldset',
                'template': 'Amasty_RmaAutomaticShippingLabel/form/shipping/fieldset',
                'collapsible': true
            });
        }
    });
});
