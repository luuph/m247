define([
    'jquery',
    'prototype',
    'mage/adminhtml/events',
    'mage/adminhtml/form'
], function (jQuery, Prototype, Events, Form) {
    window.CityUpdater = Class.create();
    CityUpdater.prototype = {
        initialize: function (countryEl, regionSelectEl, regionTextEl, cityTextEl, citySelectEl, citiesConfig, disableAction, clearCityValueOnHide) {
            this.countryEl = $(countryEl);
            this.regionSelectEl = $(regionSelectEl);
            this.regionTextEl = $(regionTextEl);
            this.cityTextEl = $(cityTextEl);
            this.citySelectEl = $(citySelectEl);
            this.citiesConfig = citiesConfig;
            this.sameAsBillingConfig = jQuery('#order-shipping_same_as_billing');

            this.disableAction = (typeof disableAction === 'undefined') ? 'hide' : disableAction;
            this.clearCityValueOnHide = (typeof clearCityValueOnHide == 'undefined') ? true : clearCityValueOnHide;

            if (this.citySelectEl.options.length <= 1) {
                this.update();
                this.syncCityValue();
            } else {
                this.lastRegionSelectId = this.regionSelectEl.value;
            }

            this.regionTextEl.changeUpdater = this.update.bind(this);
            this.regionSelectEl.changeUpdater = this.update.bind(this);
            if (this.sameAsBillingConfig.length) {
                this.sameAsBillingConfig = this.update.bind(this);
            }
            this.sameAsBillingConfig.changeUpdater = this.update.bind(this);
            if (this.sameAsBillingConfig.length) {
                Event.observe(this.sameAsBillingConfig, 'change', this.syncCityValue.bind(this));
            }
            this._makeCitySelectRequired();
            Event.observe(this.regionSelectEl, 'change', this.update.bind(this));
            Event.observe(this.countryEl, 'change', this.update.bind(this));

            Event.observe(this.citySelectEl, 'change', this.syncCityValue.bind(this));
            Event.observe(this.cityTextEl, 'change', this.syncCityValue.bind(this));
        },

        syncCityValue: function () {
            if (this.citySelectEl.visible()) {
                if (this.citySelectEl.options[this.citySelectEl.selectedIndex].value !== '') {
                    this.cityTextEl.value = this.citySelectEl.options[this.citySelectEl.selectedIndex].text;
                } else {
                    this.cityTextEl.value = '';
                }
            } else {
                this.citySelectEl.selected = false;
            }
        },

        update: function () {
            var option, city, def, cityId, selectedRegionHasCities;
            if (this.regionSelectEl.disabled) {
                this.regionSelectEl.value = '';
            }

            selectedRegionHasCities = this.citiesConfig[this.regionSelectEl.value];
            if (selectedRegionHasCities) {
                if (this.lastRegionSelectId != this.regionSelectEl.value) {
                    def = this.citySelectEl.getAttribute('defaultValue');
                    if (this.cityTextEl) {
                        if (!def) {
                            def = this.cityTextEl.value.toLowerCase();
                        }
                        this.cityTextEl.value = '';
                    }

                    this.citySelectEl.options.length = 1;
                    for (cityId in this.citiesConfig[this.regionSelectEl.value]) {
                        city = this.citiesConfig[this.regionSelectEl.value][cityId];
                        option = document.createElement('OPTION');
                        option.value = cityId;
                        option.text = city.name.stripTags();
                        option.title = city.name;
                        if (this.citySelectEl.options.add) {
                            this.citySelectEl.options.add(option);
                        } else {
                            this.citySelectEl.appendChild(option);
                        }
                        if (cityId == def
                            || city.name.toLowerCase() == def
                            || (city.code && city.code.toLowerCase() == def)
                        ) {
                            this.citySelectEl.value = cityId;
                        }
                    }
                }

                if (this.disableAction == 'hide') { //eslint-disable-line eqeqeq
                    if (this.cityTextEl) {
                        this.cityTextEl.style.display = 'none';
                        this.cityTextEl.style.disabled = true;
                    }
                    this.citySelectEl.style.display = '';
                    this.citySelectEl.disabled = false;
                } else if (this.disableAction == 'disable') { //eslint-disable-line eqeqeq
                    if (this.cityTextEl) {
                        this.cityTextEl.disabled = true;
                    }
                    this.citySelectEl.disabled = false;
                }

                this.showCitySelectEl(this.citySelectEl);
                this.lastRegionSelectId = this.regionSelectEl.value;
            } else {
                if (this.disableAction == 'hide') { //eslint-disable-line eqeqeq
                    if (this.cityTextEl) {
                        this.cityTextEl.style.display = '';
                        this.cityTextEl.style.disabled = false;
                        this.cityTextEl.value = '';
                    }
                    this.citySelectEl.value = '';
                    this.citySelectEl.style.display = 'none';
                    this.citySelectEl.disabled = true;
                } else if (this.disableAction == 'disable') { //eslint-disable-line eqeqeq
                    if (this.cityTextEl) {
                        this.cityTextEl.disabled = false;
                    }
                    this.citySelectEl.disabled = true;
                } else if (this.disableAction == 'nullify') { //eslint-disable-line eqeqeq
                    this.citySelectEl.options.length = 1;
                    this.citySelectEl.value = '';
                    this.citySelectEl.selectedIndex = 0;
                    this.lastRegionSelectId = '';
                }
                this.hideCitySelectEl(this.citySelectEl);
            }
            varienGlobalEvents.fireEvent("address_city_changed", this.regionSelectEl);
            this._makeCitySelectRequired();
        },

        _makeCitySelectRequired: function () {
            if (this.citySelectEl.parentNode.parentNode.hasClassName('_required')) {
                if (this.citySelectEl.style.display != "none") {
                    this.citySelectEl.addClassName('required-entry');
                } else {
                    this.citySelectEl.removeClassName('required-entry');
                    if ($(this.citySelectEl.id + '-error') != undefined) {
                        $(this.citySelectEl.id + '-error').remove();
                    }
                }

                if (this.cityTextEl.style.display != "none") {
                    this.cityTextEl.addClassName('required-entry');
                } else {
                    this.cityTextEl.removeClassName('required-entry');
                    if ($(this.cityTextEl.id + '-error') != undefined) {
                        $(this.cityTextEl.id + '-error').remove();
                    }
                }
            }
        },

        hideCitySelectEl: function (elem) {
            this.showHideElement(elem, false);
        },

        showCitySelectEl: function (elem) {
            this.showHideElement(elem, true);
        },

        showHideElement: function (elem, display) {
            if (elem.parentNode.parentNode) {
                var marks = Element.select(elem.parentNode.parentNode, '.required');
                if (marks[0]) {
                    display ? marks[0].show() : marks[0].hide();
                }
            }
        },

        _checkRegionRequired: function () {
            var label, wildCard, elements, that, cityRequired = true;
            elements = [this.cityTextEl, this.citySelectEl];
            that = this;
            elements.each(function (currentElement) {
                var form, validationInstance, field, topElement;
                if (!currentElement) {
                    return;
                }
                form = currentElement.form;
                validationInstance = form ? jQuery(form).data('validation') : null;
                field = currentElement.up('.field') || new Element('div');

                if (validationInstance) {
                    validationInstance.clearError(currentElement);
                }
                label = $$('label[for="' + currentElement.id + '"]')[0];
                if (label) {
                    wildCard = label.down('em') || label.down('span.required');
                    topElement = label.up('tr') || label.up('li');
                }
                if (label && wildCard) {
                    wildCard.show();
                }
                if (!currentElement.visible()) {
                    if (field.hasClassName('required')) {
                        field.removeClassName('required');
                    }

                    if (currentElement.hasClassName('required-entry')) {
                        currentElement.removeClassName('required-entry');
                    }

                    if (currentElement.tagName.toLowerCase() == 'select' && //eslint-disable-line eqeqeq
                        currentElement.hasClassName('validate-select')
                    ) {
                        currentElement.removeClassName('validate-select');
                    }
                } else {
                    if (!field.hasClassName('required')) {
                        field.addClassName('required');
                    }

                    if (!currentElement.hasClassName('required-entry')) {
                        currentElement.addClassName('required-entry');
                    }

                    if (currentElement.tagName.toLowerCase() == 'select' && //eslint-disable-line eqeqeq
                        !currentElement.hasClassName('validate-select')
                    ) {
                        currentElement.addClassName('validate-select');
                    }
                }
            });
        }
    }
});
