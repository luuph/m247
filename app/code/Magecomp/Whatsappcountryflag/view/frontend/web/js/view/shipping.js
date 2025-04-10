/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t
) {
    'use strict';

    var popUp = null;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item',
            imports: {
                countryOptions: '${ $.parentName }.shippingAddress.shipping-address-fieldset.country_id:indexedOptions'
            }
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

        /**
         * @return {exports}
         */
        initialize: function () {
            console.log('init');
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();

            if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            }
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData, changes) {
                    var isStreetAddressDeleted, isStreetAddressNotEmpty;

                    /**
                     * In last modifying operation street address was deleted.
                     * @return {Boolean}
                     */
                    isStreetAddressDeleted = function () {
                        var change;

                        if (!changes || changes.length === 0) {
                            return false;
                        }

                        change = changes.pop();

                        if (_.isUndefined(change.value) || _.isUndefined(change.oldValue)) {
                            return false;
                        }

                        if (!change.path.startsWith('shippingAddress.street')) {
                            return false;
                        }

                        return change.value.length === 0 && change.oldValue.length > 0;
                    };

                    isStreetAddressNotEmpty = shippingAddrsData.street && !_.isEmpty(shippingAddrsData.street[0]);

                    if (isStreetAddressNotEmpty || isStreetAddressDeleted()) {
                        checkoutData.setShippingAddressFromData(shippingAddrsData);
                    }
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            return this;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {        
                var countryList = { 
                    'AF': '93', 'AX': '358', 'AL': '355', 'DZ': '213',
                    'AS': '1', 'AD': '376', 'AO': '244', 'AI': '1',
                    'AQ': '672', 'AG': '1', 'AR': '54', 'AM': '374',
                    'AU': '61', 'AT': '43', 'AZ': '994', 'BS': '1',
                    'BH': '973', 'BD': '880', 'BB': '1', 'BY': '375',
                    'BE': '32', 'BZ': '501', 'BJ': '229', 'BM': '1',
                    'BT': '975', 'BO': '591', 'BA': '387', 'BW': '267',
                    'BR': '55', 'BG': '359', 'BF': '226', 'BI': '257',
                    'KH': '855', 'CM': '237', 'CA': '1', 'CV': '238',
                    'CF': '236', 'TD': '235', 'CL': '56', 'CN': '86',
                    'CO': '57', 'KM': '269', 'CG': '242', 'CD': '243',
                    'CR': '506', 'CI': '225', 'HR': '385', 'CU': '53',
                    'CY': '357', 'CZ': '420', 'DK': '45', 'DJ': '253',
                    'DM': '1', 'DO': '1', 'EC': '593', 'EG': '20',
                    'SV': '503', 'GQ': '240', 'ER': '291', 'EE': '372',
                    'ET': '251', 'FI': '358', 'FR': '33', 'DE': '49',
                    'GH': '233', 'GR': '30', 'IN': '91', 'US': '1',
                    'AW': '297', 'BV': null, 'IO': '246', 'VG': '1',
                    'BN': '673', 'BQ': '599', 'KY': '1',  'CX': '61',
                    'CC': '61', 'CK': '682', 'CW': '599', 'SZ': '268',
                    'FK': '500', 'FO': '298', 'FJ': '679',
                    'GF': '594', 'PF': '689', 'TF': null, 'GA': '241',
                    'GM': '220', 'GE': '995', 'GI': '350', 'GL': '299',
                    'GD': '1', 'GP': '590', 'GU': '1', 'GT': '502',
                    'GG': '44', 'GN': '224', 'GW': '245', 'GY': '592',
                    'HT': '509', 'HM': null, 'HN': '504', 'HK': '852',
                    'HU': '36', 'IS': '354', 'ID': '62', 'IR': '98',
                    'IQ': '964', 'IE': '353', 'IM': '44', 'IL': '972',
                    'IT': '39', 'JM': '1', 'JP': '81', 'JE': '44',
                    'JO': '962', 'KZ': '7', 'KE': '254', 'KI': '686',
                    'KW': '965', 'KG': '996', 'LA': '856', 'LV': '371',
                    'LB': '961', 'LS': '266', 'LR': '231', 'LY': '218',
                    'LI': '423', 'LT': '370', 'LU': '352', 'MO': '853',
                    'MG': '261', 'MW': '265', 'MY': '60', 'MV': '960',
                    'ML': '223', 'MT': '356', 'MH': '692', 'MQ': '596',
                    'MR': '222', 'MU': '230', 'YT': '262', 'MX': '52',
                    'FM': '691', 'MD': '373', 'MC': '377', 'MN': '976',
                    'ME': '382', 'MS': '1', 'MA': '212', 'MZ': '258',
                    'MM': '95', 'NA': '264', 'NR': '674', 'NP': '977',
                    'NL': '31', 'NC': '687', 'NZ': '64', 'NI': '505',
                    'NE': '227', 'NG': '234', 'NU': '683', 'NF': '672',
                    'KP': '850', 'MK': '389', 'MP': '1', 'NO': '47',
                    'OM': '968', 'PK': '92', 'PW': '680', 'PS': '970',
                    'PA': '507', 'PG': '675', 'PY': '595', 'PE': '51',
                    'PH': '63', 'PN': '872', 'PL': '48', 'PT': '351',
                    'PR': '1', 'QA': '974', 'RE': '262', 'RO': '40',
                    'RU': '7', 'RW': '250', 'WS': '685', 'SM': '378',
                    'ST': '239', 'SA': '966', 'SN': '221', 'RS': '381',
                    'SC': '248', 'SL': '232', 'SG': '65', 'SX': '1',
                    'SK': '421', 'SI': '386', 'SB': '677', 'SO': '252',
                    'ZA': '27', 'GS': null, 'KR': '82', 'SS': '211',
                    'ES': '34', 'LK': '94', 'BL': '590', 'KN': '1',
                    'LC': '1', 'MF': '590', 'PM': '508', 'VC': '1',
                    'SD': '249', 'SR': '597', 'SJ': '47', 'SE': '46',
                    'CH': '41', 'SY': '963', 'TW': '886', 'TJ': '992',
                    'TZ': '255', 'TH': '66', 'TL': '670', 'TG': '228',
                    'TK': '690', 'TO': '676', 'TT': '1', 'TN': '216',
                    'TR': '90', 'TM': '993', 'TC': '1', 'TV': '688',
                    'UG': '256', 'UA': '380', 'AE': '971', 'GB': '44',
                    'UM': '1', 'UY': '598', 'UZ': '998', 'VU': '678',
                    'VA': '379', 'VE': '58', 'VN': '84', 'WF': '681',
                    'EH': '212', 'YE': '967', 'ZM': '260', 'ZW': '263'
                };

                var mobile = jQuery('input[name="telephone"]').val();
                if (Object.values(countryList).includes(mobile)) {
                    jQuery('input[name="telephone"]').addClass('mage-error');
                    $(".field-note").css('color', 'red').text($.mage.__('This is a required field.'));
                    return false;
                } else {
                    console.log(mobile + " does not exist in countryList");
                }
          
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');

                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },
        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return checkoutData.getSelectedShippingRate() ? checkoutData.getSelectedShippingRate() :
                quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });
                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                    }
                );
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            console.log('validateShippingInformation');
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                option = _.isObject(this.countryOptions) && this.countryOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (!quote.shippingMethod()['method_code']) {
                    this.errorValidationMessage(
                        $t('The shipping method is missing. Select the shipping method and try again.')
                    );
                }

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').trigger('focus');

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        }
    });
});