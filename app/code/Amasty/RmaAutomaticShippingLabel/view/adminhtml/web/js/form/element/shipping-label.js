define([
    'Amasty_Rma/js/form/element/file-uploader',
    'uiRegistry',
    'jquery',
    'Amasty_RmaAutomaticShippingLabel/js/action/notification',
    'mage/translate'
], function (fileUploader, registry, $, notification, $t) {
    'use strict';

    return fileUploader.extend({
        defaults: {
            shippingInformationName: 'amrma_request_form.amrma_request_form.shipping-information',
            showLabel: 'amrma_request_form.amrma_request_form.show-label',
            carriersError: $t('Carriers not found.'),
            generateSelector: '[data-amshiping-js="generate"]',
            rmaTemplate: '',
            packagesData: [],
            rmaItems: [],
            itemStateApproved: 1,
            stateApproved: 4,
            statusRma: ''
        },

        initialize: function () {
            this._super();

            if (this.statusRma < this.stateApproved || !this.hasApprovedItems()) {
                this.template = this.rmaTemplate;
            }
        },

        initObservable: function () {
            this._super().observe([
                'packagesData'
            ]);

            return this;
        },

        hasApprovedItems: function () {
            return this.rmaItems.some(function (items) {
                return items.some(function (item) {
                    return item.status === this.itemStateApproved;
                }.bind(this));
            }.bind(this));
        },

        generateLabel: function () {
            registry.async(this.shippingInformationName)(function (modal) {
                this.initModal(modal);
            }.bind(this));
        },

        viewPackage: function () {
            var self = this,
                data;

            $.ajax({
                url: this.viewPackageUrl,
                data: { 'request_id': this.request_id },
                showLoader: true,
                method: 'get',
                dataType: 'json',
                success: function (res) {
                    data = JSON.parse(res);
                    self.openLabel(data);
                }
            });
        },

        openLabel: function (data) {
            registry.async(this.showLabel)(function (modal) {
                modal.packagesData = data;
                modal.openModal();
                modal.initializeLabel(data);
            });
        },

        initModal: function (modal) {
            if (modal.elems().length) {
                modal.openModal();

                return;
            }

            this.getCarriers(modal);
        },

        getCarriers: function (modal) {
            var self = this;

            $.ajax({
                url: this.carriersUrl,
                data: { 'request_id': this.request_id },
                showLoader: true,
                method: 'get',
                dataType: 'json',
                success: function (res) {
                    if (!Object.keys(res).length) {
                        notification.add(self.carriersError, false, self.generateSelector);

                        return;
                    }

                    notification.clear();
                    modal.openModal();
                    modal.initCarriers(res);
                }
            });
        },

        hasLabel: function (files) {
            return files.some(function (file) {
                return file.isGenerated;
            });
        }
    });
});
