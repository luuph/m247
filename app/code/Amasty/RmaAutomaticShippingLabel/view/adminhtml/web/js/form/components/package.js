define([
    'Magento_Ui/js/form/components/fieldset',
    'jquery',
    'mage/translate',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'Amasty_RmaAutomaticShippingLabel/js/action/notification'
], function (Fieldset, $, $t, ko, _, message, notification) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            packageTmpl: 'Amasty_RmaAutomaticShippingLabel/form/package/package',
            itemsTmpl: 'Amasty_RmaAutomaticShippingLabel/form/package/items_grid',
            items: [],
            isEditPackage: false,
            itemStateApproved: 1,
            selectors: {
                row: '[data-amshiping-js="row"]',
                addBtn: '.amshipping-add-button',
                doneBtn: '.amshipping-done-button',
                wrapper: '.amshipping-modal-component',
                weight: '[data-amshipping-js="weight-%s"]',
                customsValue: '[data-amshipping-js="customs_value-%s"]'
            },
            state: {
                active: '-active',
                mageDisabled: 'disabled'
            },
            packages: [],
            renderPackages: [],
            applyShippingLabel: '',
            shippingInformation: {},
            isShowCustomsValue: false
        },

        initObservable: function () {
            this._super().observe([
                'items',
                'isShowItems',
                'renderPackages',
                'isEditPackage',
                'shippingInformation',
                'isShowCustomsValue'
            ]);

            return this;
        },

        initializePackages: function () {
            this.addBtn = $(this.selectors.addBtn);
            this.doneBtn = $(this.selectors.doneBtn);
            this.modalWrapper = $(this.selectors.wrapper);
            this.isShowCustomsValue = this.getIsShowCustomsValue();
        },

        getIsShowCustomsValue: function () {
            var isShowCustomsValue = false;

            this.packagesData.each(function (item) {
                if (item.value === 'customs_value') {
                    isShowCustomsValue = item.isHidden;
                }
            });

            return isShowCustomsValue;
        },

        getPackageLabel: function (index) {
            return $t('Package') + ' ' + (index + 1);
        },

        getUniqueId: function (prefix) {
            return _.uniqueId(prefix);
        },

        /**
         * Create new package
         *
         * @param {Object} items
         * @param {Boolean} isAfterDelete
         */
        createPackage: function (items, isAfterDelete) {
            var newPackage = {};

            if (!items && this.packages.length) {
                return;
            }

            newPackage.items = ko.observableArray(items || this.getDefaultItems());
            newPackage.index = this.packages.length;
            newPackage.isShowItems = ko.observable(false);
            newPackage.isItemsSelected = ko.observable(false);
            newPackage.params = {};
            this.prepareItems(newPackage.items);

            if (isAfterDelete) {
                newPackage.isShowItems(true);
            }

            this.packages.push(newPackage);
            this.renderPackages(this.packages);
            this.isEditPackage(true);
        },

        /**
         * Add uniqueId and calculate default qty
         *
         * @param {Object} items
         */
        prepareItems: function (items) {
            items.each(function (item) {
                if (!item.selectedQty) {
                    item.selectedQty = 0;
                }

                item.uniqueId = this.getUniqueId(item.sku);
                item.packageQty = item.qty - item.selectedQty;
            }.bind(this));
        },

        validateItemQty: function (event, item) {
            if (item.qty < +item.packageQty + item.selectedQty) {
                $(event.currentTarget).val(item.qty - item.selectedQty);
            }
        },

        getApprovedItems: function (items) {
            return ko.toJS(items).filter(function (item) {
                return item.status === this.itemStateApproved;
            }.bind(this));
        },

        getDefaultItems: function () {
            return this.getApprovedItems(this.concatItems());
        },

        concatItems: function () {
            return this.items().reduce(function (sum, item) {
                return sum.concat(item);
            });
        },

        showProducts: function (currentPackage) {
            currentPackage.isShowItems(true);
        },

        /**
         * Calculate checked and rest items
         * render package after add items to package
         *
         * @param {int} index
         */
        prepareProducts: function (index) {
            var checkedItems = this.renderPackages()[index].items.filter(function (item) {
                return item.checked;
            });

            if (!checkedItems.length) {
                message({ content: this.messages.checkedProducts });

                return;
            }

            if (!this.validate(checkedItems)) {
                message({ content: this.messages.quantity });

                return;
            }

            checkedItems = this.cloneAndModify(checkedItems, index);

            this.restItems = this.getRestItems(this.renderPackages()[index].items());
            this.restItems = this.cloneAndModify(this.restItems);
            this.preparePackages(checkedItems, index, true, false);

            this.isEditPackage(false);

            $(this.selectors.row + '.' + this.state.active)
                .removeClass(this.state.active).addClass(this.state.mageDisabled);
            this.doneBtn.removeClass(this.state.mageDisabled);

            if (this.restItems.length) {
                this.addBtn.removeClass(this.state.mageDisabled);
            }
        },

        /**
         * Calculate rest items
         *
         * @param {Object} items
         * @return Boolean
         */
        getRestItems: function (items) {
            return items.filter(function (item) {
                if (!item.checked) {
                    return true;
                }

                if (item.qty === +item.selectedQty + +item.packageQty) {
                    item.selectedQty = item.qty;

                    return false;
                }

                item.selectedQty = +item.selectedQty + +item.packageQty;
                item.checked = false;

                return true;
            });
        },

        cloneAndModify: function (array, index) {
            return array.map(function (item) {
                item = _.clone(item);

                if (typeof index !== 'undefined') {
                    item.totalQty = item.packageQty;
                    item.saved = true;
                    item.parentIndex = index;
                }

                return item;
            });
        },

        validate: function (items) {
            return items.every(function (item) {
                return item.packageQty && item.qty >= item.packageQty;
            });
        },

        checkItem: function (item, event) {
            $(event.currentTarget).closest(this.selectors.row).toggleClass(this.state.active);
        },

        actionAddPackage: function () {
            this.createPackage(this.restItems);
            this.disableTopBtns();
        },

        disableTopBtns: function () {
            this.addBtn.addClass(this.state.mageDisabled);
            this.doneBtn.addClass(this.state.mageDisabled);
        },

        removeItem: function (item) {
            this.addDeletedItems(item.parentIndex);
            this.disableTopBtns();
            this.preparePackages(this.restItems, item.parentIndex, false, true);
            this.isEditPackage(true);
        },

        deletePackage: function (index) {
            this.addDeletedItems(index);
            this.packages.splice(index, 1);
            this.renderPackages(this.packages);
            this.addBtn.removeClass(this.state.mageDisabled);
        },

        preparePackages: function (items, index, isItemsSelected, isShowItems) {
            var totalWeight = this.getTotalWeight(items, index),
                customsValue = this.getTotalCustomsValue(items, index);

            this.packages[index].items(items);
            this.packages[index].isItemsSelected(isItemsSelected);
            this.packages[index].isShowItems(isShowItems);
            this.renderPackages(this.packages);
            this.modalWrapper.find(this.selectors.weight.replace('%s', index)).val(totalWeight);
            this.modalWrapper.find(this.selectors.customsValue.replace('%s', index)).val(customsValue);
        },

        getTotalWeight: function (items) {
            var totalWeight = 0;

            items.forEach(function (item) {
                totalWeight += item.weight * item.packageQty;
            });

            return totalWeight;
        },

        getTotalCustomsValue: function (items) {
            var totalCustomsValue = 0;

            items.forEach(function (item) {
                totalCustomsValue += item.customs_value * item.packageQty;
            });

            return totalCustomsValue;
        },

        uncheckedRestItems: function () {
            this.restItems = this.restItems.map(function (item) {
                item.checked = false;
                item.selectedQty = this.findRealSelectedQty(item.sku) - item.packageQty;

                return item;
            }.bind(this));
        },

        /**
         * Calculate selected product qty with all items
         *
         * @param {string} sku
         * @return int
         */
        findRealSelectedQty: function (sku) {
            var qty = 0;

            this.packages.forEach(function (packageItem) {
                packageItem.items().forEach(function (item) {
                    if (item.sku === sku) {
                        qty += +item.packageQty;
                    }
                });
            });

            return qty;
        },

        /**
         * Calculate restItems after deleting an item
         *
         * @param {int} index
         */
        addDeletedItems: function (index) {
            var isFind;

            if (!this.restItems.length) {
                this.restItems = this.packages[index].items();
                this.uncheckedRestItems();

                return;
            }

            this.packages[index].items().forEach(function (removedItem) {
                isFind = false;
                this.restItems = this.restItems.map(function (item) {
                    if (removedItem.sku === item.sku) {
                        var qty = this.findRealSelectedQty(item.sku);

                        item.selectedQty = qty - removedItem.packageQty;
                        item.packageQty = item.qty - item.selectedQty;
                        isFind = true;
                    }

                    return item;
                }.bind(this));

                if (!isFind) {
                    removedItem.selectedQty = removedItem.qty - removedItem.packageQty;
                    removedItem.checked = false;
                    this.restItems.push(removedItem);
                }
            }.bind(this));
        },

        /**
         * Preparing data for sending to the server
         *
         * @return Array
         */
        getPackages: function () {
            return this.renderPackages().map(function (packageItem) {
                packageItem.items = ko.toJS(packageItem.items);

                if (!packageItem.params.weight) {
                    packageItem.params.weight = this.getTotalWeight(packageItem.items);
                }

                if (!packageItem.params.customs_value) {
                    packageItem.params.customs_value = this.getTotalCustomsValue(packageItem.items);
                }

                packageItem.packageLabels = this.getPackageParams(packageItem.params);

                return packageItem;
            }.bind(this));
        },

        getPackageParams: function (params) {
            var renderParams = [];

            this.packagesData.each(function (item) {
                renderParams.push({
                    label: item.label,
                    value: params[item.value],
                    isHidden: Boolean(item.isHidden)
                });
            });

            return renderParams;
        },

        validatePackages: function (packages) {
            return packages.every(function (packageItem) {
                return this.packagesData.length > Object.keys(packageItem.params).length;
            }.bind(this));
        },

        revertPrepareData: function () {
            return this.renderPackages().map(function (packageItem) {
                packageItem.items = ko.observableArray(packageItem.items);

                return packageItem;
            });
        },

        actionDone: function () {
            var self = this,
                data = this.shippingInformation();

            data.packages = this.getPackages();

            if (this.validatePackages(data.packages)) {
                message({ content: this.messages.inputfields });

                return;
            }

            $.ajax({
                url: this.applyShippingLabel,
                data: {
                    data: data,
                    request_id: this.request_id
                },
                showLoader: true,
                method: 'post',
                dataType: 'json',
                success: function (res) {
                    if (Object.keys(res).length) {
                        notification.add(res.message, res.error);
                        self.revertPrepareData();

                        return;
                    }

                    location.reload();
                }
            });
        }
    });
});
