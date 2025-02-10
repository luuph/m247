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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'jquery/file-uploader',
    'mage/translate',
    'mage/backend/notification',
    'Bss_CustomOptionTemplate/js/jquery.magnific-popup',
    'Magento_Catalog/js/custom-options',
    'mage/adminhtml/form',
    'Magento_Catalog/js/form/element/input',
], function ($, mageTemplate, alert) {
    'use strict';
    $.validator.addMethod(
        "dependent-id-exist",
        function(value, element) {
            if (value === '') {
                return true;
            }
            var ids = value.split(','),
                result = true;
            $.each(ids, function (k, val) {
                if (window.bss_depend_id[val] === undefined) {
                    result = false;
                }
            });
            return result;
        },
        $.mage.__("Please add the valid IDs")
    );
    $.validator.addMethod(
        "dependent-id-option",
        function(value, element) {
            var ids = value.split(','),
                result = true,
                currentIds = window.bss_depend_option[$(element).attr('option_key')];
            $.each(currentIds, function (k, id) {
                if (ids.indexOf(k) >= 0) {
                    result = false;
                }
            });
            return result;
        },
        $.mage.__("Parent and children values can't be in the same custom option")
    );
    $.widget('bss.customOptions', $.mage.customOptions, {
        /** @inheritdoc */
        _create: function () {
            var $widget = this;
            if (window.bss_depend_id === undefined) {
                window.bss_depend_id = {};
            }
            if (window.bss_depend_option === undefined) {
                window.bss_depend_option = {};
            }
            this.baseTmpl = mageTemplate('#custom-option-base-template');
            this.rowTmpl = mageTemplate('#custom-option-select-type-row-template');
            this.rowTierPrice = mageTemplate('#custom-option-row-tier-price');
            this.itemsCount = 0;
            $('body').on('click', '.dco-button', function () {
                $widget.multiselectOption($(this));
            });
            $('body').on('input, change','.multiselect-dco', function () {
                var val = $widget.multiselectChange($(this));
                $(this).next().val(val.toString())
            });
            $('body').on('input, change','.value-is-default', function () {
                var listRadioType = ['drop_down','radio'];
                var checkType = $(this).closest('fieldset').find('.select-product-option-type').val();

                if ($(this).prop('checked')) {
                    if ($.inArray(checkType,listRadioType) > -1) {
                        var checkData = $(this).attr('data-option-value-isdefault');
                        $('input[data-option-value-isdefault="'+checkData+'"]').not($(this)).prop('checked',false).val('0');
                    }
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
            $('body').on('input, change','.select-product-option-type', function () {
                var listDrop = ['drop_down', 'radio'];
                if ($.inArray($(this).val(), listDrop) > -1) {
                    $(this).closest('.fieldset-wrapper-content').find('.swatch-image-container').parent().show();
                    $(this).closest('.fieldset-wrapper-content').find('.swatch-image-coi').show();
                } else {
                    $(this).closest('.fieldset-wrapper-content').find('.swatch-image-container').parent().hide();
                    $(this).closest('.fieldset-wrapper-content').find('.swatch-image-coi').hide();
                }
                $(".image-customoption-container .preview img").each(function(){
                    if ($(this).attr('src') !== '') {
                        $(this).closest('.image-customoption-container').find('.image-placeholder').hide();
                    } else {
                        $(this).closest('.image-customoption-container').find('.image-placeholder').show();
                    }
                });
                var checkDataFirstLoad = $(this).closest('.fieldset-wrapper').find('.admin__actions-switch-label').data('data-check-first-load');
                if (checkDataFirstLoad !== undefined && checkDataFirstLoad != 1) {
                    $(this).closest('.fieldset-wrapper').find('.value-is-default').prop('checked',false).val('0');
                } else {
                    $(this).closest('.fieldset-wrapper').find('.admin__actions-switch-label').data('data-check-first-load', 0);
                }
            });

            //
            $('body').on('click','.button-show-modal', function () {
                var self = this;
                var dataVisibleCustomer = $(this).closest('.template-div-modal').find('input.data-visible-customer').val();
                var dataVisibleStore = $(this).closest('.template-div-modal').find('input.data-visible-store').val();
                if (dataVisibleCustomer) {
                    dataVisibleCustomer = dataVisibleCustomer.split(',');
                    $(this).closest('.template-div-modal').find('.visible-customer-group').val(dataVisibleCustomer);
                }
                if (dataVisibleStore) {
                    dataVisibleStore = dataVisibleStore.split(',');
                    $(this).closest('.template-div-modal').find('.visible-store-view').val(dataVisibleStore);
                }
                if ($(this).closest('.template-div-modal').find('input.data-title-store').val()) {
                    $.each(JSON.parse($(this).closest('.template-div-modal').find('input.data-title-store').val()), function (index, value) {
                        $(self).closest('.template-div-modal').find('.option-title-store[data-index="'+index+'"]').val(value);
                    });
                }
                $(this).closest('.template-div-modal').find('aside').addClass('_show');
            });
            $('body').on('click','.button-close-modal', function () {
                var dataVisibleCustomer = $(this).closest('.template-div-modal').find('.visible-customer-group').val();
                var dataVisibleStore = $(this).closest('.template-div-modal').find('.visible-store-view').val();
                var dataTitleStores = {};
                $(this).closest('.template-div-modal').find('.option-title-store').each(function () {
                    if ($(this).val()) {
                        dataTitleStores[$(this).attr('data-index')] = $(this).val();
                    }
                });
                if (dataVisibleCustomer) {
                    dataVisibleCustomer = dataVisibleCustomer.toString();
                    $(this).closest('.template-div-modal').find('input.data-visible-customer').val(dataVisibleCustomer);
                } else {
                    $(this).closest('.template-div-modal').find('input.data-visible-customer').val('');
                }
                if (dataVisibleStore) {
                    dataVisibleStore = dataVisibleStore.toString();
                    $(this).closest('.template-div-modal').find('input.data-visible-store').val(dataVisibleStore);
                } else {
                    $(this).closest('.template-div-modal').find('input.data-visible-store').val('');
                }
                if (dataTitleStores) {
                    dataTitleStores = JSON.stringify(dataTitleStores);
                    $(this).closest('.template-div-modal').find('input.data-title-store').val(dataTitleStores);
                }

                $(this).closest('.template-div-modal').find('aside').removeClass('_show');
            });

            $('body').on('input, change','.field-option-title .input-text', function () {
                $('.dco-button').each(function (index) {
                    $widget.multiselectOption($(this));
                });
                var id = $(this).closest('.determined-location').find('.dependent-id-option').val() || $(this).closest('.determined-location').find('.dependent-id').val();
                $('.multiselect-dco option[value="'+id+'"]').text($(this).val());
            });
            $('body').on('click','.actions .action-delete', function () {
                $(this).closest('.fieldset-wrapper').find('.dependent-id').each(function (index) {
                    delete window.bss_depend_id[$(this).val()];
                });
                $('.dco-button').each(function (index) {
                    $widget.multiselectOption($(this));
                });
            });
            $('body').on('click','.delete-select-row', function () {
                $(this).closest('tr').find('.dependent-id').each(function (index) {
                    delete window.bss_depend_id[$(this).val()];
                });
                $('.dco-button').each(function (index) {
                    $widget.multiselectOption($(this));
                });
            });

            //Logic Tier Price Option
            jQuery("body").on("click", '.option-tier-price-div .add-tier-price-button', function(event){
                var elementOptionTier = $(this).parent();
                var tierPriceData = elementOptionTier.find('.ahii').val();
                var data = {};
                jQuery(this).parent().find('aside').addClass('_show');
                data[5] = elementOptionTier.find('.ahii').attr('name');
                data[6] = elementOptionTier.find('.tbody-tier-price-data');
                if (tierPriceData) {
                    jQuery.each(JSON.parse(tierPriceData), function (key, value) {
                        $widget.addTierPriceItem(value['website_id'],value['cust_group'],value['price_qty'],value['price-type'],value['price'], data[5], data[6]);
                    });
                }
                return false;
            });

            // submit tier price of option to input
            jQuery("body").on("click", '.option-tier-price-div .action-close, .option-tier-price-div .action-primary', function(event){
                $widget.checkandAddData($widget, this)
            });
            //add new tier price row
            jQuery("body").on("click", '.add-tier-price-row', function(event){
                var data = {};
                data[5] = $(this).closest('.option-tier-price-div').find('.ahii').attr('name');
                data[6] = $(this).closest('.option-tier-price-div').find('.tbody-tier-price-data');
                $widget.addTierPriceItem(0, 32000, '', 'fixed', '',data[5], data[6]);
            });
            //change price type of tier price will change symbol
            jQuery("body").on("change", '.option-tier-price-div .price-type', function(event){
                var symbolElement = $(this).closest('.admin__field-control').find('.bss-abs-symbol span');
                if ($(this).val() === 'fixed') {
                    symbolElement.text(window.bss_currency_symbol);
                } else {
                    symbolElement.text('%');
                }
            });
            // remove tier price row of option
            jQuery("body").on("click", '.option-tier-price-div .action-delete.option-tier-price', function(event){
                event.stopImmediatePropagation();
                jQuery(this).parentsUntil('tr').parent().remove();
                return false;
            });
            this._initOptionBoxes();
            this._initSortableSelections();
            this._bindCheckboxHandlers();
            this._bindCheckboxHandlers2();
            this._bindReadOnlyMode();
            this._addValidation();
            this._afterClickReload();
        },
        _initOptionBoxes: function () {
            var syncOptionTitle;

            if (!this.options.isReadonly) {
                this.element.sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    items: '#product_options_container_top > div',
                    update: this._updateOptionBoxPositions,
                    tolerance: 'pointer'
                });
            }

            /**
             * @param {jQuery.Event} event
             */
            syncOptionTitle = function (event) {
                var currentValue = $(event.target).val(),
                    optionBoxTitle = $(
                        '.admin__collapsible-title > span',
                        $(event.target).closest('.fieldset-wrapper')
                    ),
                    newOptionTitle = $.mage.__('New Option');

                optionBoxTitle.text(currentValue === '' ? newOptionTitle : currentValue);
            };
            this._on({
                /**
                 * Reset field value to Default
                 */
                'click .use-default-label': function (event) {
                    $(event.target).closest('label').find('input').prop('checked', true).trigger('change');
                },

                /**
                 * Remove custom option or option row for 'select' type of custom option
                 */
                'click button[id^=product_option_][id$=_delete]': function (event) {
                    var element = $(event.target).closest('#product_options_container_top > div.fieldset-wrapper,tr');

                    if (element.length) {
                        $('#product_' + element.attr('id').replace('product_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate').hide();
                        this.refreshSortableElements();
                    }
                },

                /**
                 * Minimize custom option block
                 */
                'click #product_options_container_top [data-target$=-content]': function () {
                    if (this.options.isReadonly) {
                        return false;
                    }
                },

                /**
                 * Add new custom option
                 */
                'click #add_new_defined_option': function (event) {
                    this.addOption(event);
                },

                /**
                 * Add new option row for 'select' type of custom option
                 */
                'click button[id^=product_option_][id$=_add_select_row]': function (event) {
                    this.addSelection(event);
                },

                /**
                 * Import custom options from products
                 */
                'click #import_new_defined_option': function () {
                    var importContainer = $('#import-container'),
                        widget = this;

                    importContainer.modal({
                        title: $.mage.__('Select Product'),
                        type: 'slide',

                        /** @inheritdoc */
                        opened: function () {
                            $(document).off().on('click', '#productGrid_massaction-form button', function () {
                                $('.import-custom-options-apply-button').trigger('click', 'massActionTrigger');
                            });
                        },
                        buttons: [{
                            text: $.mage.__('Import'),
                            attr: {
                                id: 'import-custom-options-apply-button'
                            },
                            'class': 'action-primary action-import import-custom-options-apply-button',

                            /** @inheritdoc */
                            click: function (event, massActionTrigger) {
                                var request = [];

                                $(this.element).find('input[name=product]:checked').map(function () {
                                    request.push(this.value);
                                });

                                if (request.length === 0) {
                                    if (!massActionTrigger) {
                                        alert({
                                            content: $.mage.__('An item needs to be selected. Select and try again.')
                                        });
                                    }

                                    return;
                                }

                                $.post(widget.options.customOptionsUrl, {
                                    'products[]': request,
                                    'form_key': widget.options.formKey
                                }, function ($data) {
                                    $.parseJSON($data).each(function (el) {
                                        var i;

                                        el.id = widget.getFreeOptionId(el.id);
                                        el['option_id'] = el.id;

                                        if (typeof el.optionValues !== 'undefined') {
                                            for (i = 0; i < el.optionValues.length; i++) {
                                                el.optionValues[i]['option_id'] = el.id;
                                            }
                                        }
                                        //Adding option
                                        widget.addOption(el);
                                        //Will save new option on server side
                                        $('#product_option_' + el.id + '_option_id').val(0);
                                        $('#option_' + el.id + ' input[name$="option_type_id]"]').val(-1);
                                    });
                                    importContainer.modal('closeModal');
                                });
                            }
                        }]
                    });
                    importContainer.load(
                        this.options.productGridUrl,
                        {
                            'form_key': this.options.formKey,
                            'current_product_id': this.options.currentProductId
                        },
                        function () {
                            importContainer.modal('openModal');
                        }
                    );
                },

                /**
                 * Change custom option type
                 */
                'change select[id^=product_option_][id$=_type]': function (event, data) {
                    var widget = this,
                        currentElement = $(event.target),
                        parentId = '#' + currentElement.closest('.fieldset-alt').attr('id'),
                        group = currentElement.find('[value="' + currentElement.val() + '"]')
                            .closest('optgroup').attr('data-optgroup-name'),
                        previousGroup = $(parentId + '_previous_group').val(),
                        previousBlock = $(parentId + '_type_' + previousGroup),
                        tmpl, disabledBlock, priceType;

                    data = data || {};

                    if (typeof group !== 'undefined') {
                        group = group.toLowerCase();
                    }

                    if (previousGroup !== group) {
                        if (previousBlock.length) {
                            previousBlock.remove();
                        }
                        $(parentId + '_previous_group').val(group);

                        if (typeof group === 'undefined') {
                            return;
                        }
                        disabledBlock = $(parentId).find(parentId + '_type_' + group);

                        if (disabledBlock.length) {
                            disabledBlock.removeClass('ignore-validate').show();
                        } else {
                            if ($.isEmptyObject(data)) { //eslint-disable-line max-depth
                                data['option_id'] = $(parentId + '_id').val();
                                data.price = data.sku = '';
                            }
                            data.group = group;

                            tmpl = widget.element.find('#custom-option-' + group + '-type-template').html();
                            tmpl = mageTemplate(tmpl, {
                                data: data
                            });

                            $(tmpl).insertAfter($(parentId));

                            if (data['price_type']) { //eslint-disable-line max-depth
                                priceType = $('#' + widget.options.fieldId + '_' + data['option_id'] + '_price_type');
                                priceType.val(data['price_type']).attr('data-store-label', data['price_type']);
                            }
                            this._bindUseDefault(widget.options.fieldId + '_' + data['option_id'], data);
                            //Add selections

                            if (data.optionValues) { //eslint-disable-line max-depth
                                data.optionValues.each(function (value) {
                                    widget.addSelection(value);
                                });
                            }
                        }
                    }
                },
                //Sync title
                'change .field-option-title > .control > input[id$="_title"]': syncOptionTitle,
                'keyup .field-option-title > .control > input[id$="_title"]': syncOptionTitle,
                'paste .field-option-title > .control > input[id$="_title"]': syncOptionTitle
            });
        },
        checkandAddData: function ($widget, $this) {
            var elementOptionTier = $($this).closest('.option-tier-price-div');
            var dataForm = elementOptionTier.find('.bss-custom-option-tier-price-form');
            dataForm.validation();
            dataForm.mage('validation', {});
            if (dataForm.validation('isValid')) {
                var data = [];
                var checkNotDupliate = true;
                elementOptionTier.find('aside table tbody tr').map(function (index, elem) {
                    var ret = {};
                    $('.inputValue', this).each(function () {
                        ret[$(this).data().checkType] = $(this).val();
                    });
                    for (var datum of data) {
                        if ($widget.compareArrPrice(ret, datum)) {
                            checkNotDupliate = false;
                            alert({
                                content: $.mage.__("We found a duplicate website, tier price, customer group and quantity.")
                            });
                            return false;
                        }
                    }
                    data.push(ret);
                });
                if (checkNotDupliate) {
                    var element = jQuery($this).closest('.option-tier-price-div').find('.ahii');
                    element.val(JSON.stringify(data));
                    element.trigger('change');
                    elementOptionTier.find('aside').removeClass('_show');
                    elementOptionTier.find('table tbody tr').remove();
                }
            }
        },
        compareArrPrice: function (inputData, dataPrice) {
            var objectsAreSame = true;
            for(var propertyName in inputData) {
                if (propertyName === 'price-type' || propertyName === 'price') {
                    continue;
                }
                if(inputData[propertyName] !== dataPrice[propertyName]) {
                    objectsAreSame = false;
                    break;
                }
            }
            return objectsAreSame;
        },

        addTierPriceItem: function() {
            var optionWebsite ='', optionGroup = '';
            jQuery.each(window.bss_list_websites_array, function (key, value) {
                optionWebsite += '<option value="'+value.value+'">'+value.label+'</option>';
            });
            jQuery.each(window.bss_list_customer_group_array, function (key, value) {
                optionGroup += '<option value="'+value.value+'">'+value.label+'</option>';
            });
            var tmpl;
            var count = this.itemsCount;
            var data = {
                website_id: 0,
                group: 32000,
                qty: '',
                price: '',
                price_type: 'fixed',
                readOnly: false,
                index: this.itemsCount++,
                symbol: window.bss_currency_symbol,
                inputName: arguments[5]
            };
            if(arguments.length > 4) {
                data.website_id = arguments[0];
                data.group      = arguments[1];
                data.qty        = arguments[2];
                data.price_type = arguments[3];
                data.price      = arguments[4];
                if (data.price_type ==='fixed') {
                    data.symbol = window.bss_currency_symbol;
                } else {
                    data.symbol = "%";
                }
            }
            tmpl = this.rowTierPrice({
                data: {
                    website_id: data.website_id,
                    group: data.group,
                    qty: data.qty,
                    price :data.price,
                    price_type: data.price_type,
                    index :data.index,
                    symbol : data.symbol,
                    inputName:data.inputName,
                    optionWebsite: optionWebsite,
                    optionGroup: optionGroup
                }
            });
            arguments[6].append(tmpl);
            var websiteCol = jQuery('[name="'+data.inputName+'['+count+'][website_id]'+'"]');
            var groupCol = jQuery('[name="'+data.inputName+'['+count+'][cust_group]'+'"]');
            var priceTypeCol = jQuery('[name="'+data.inputName+'['+count+'][price-type]'+'"]');
            websiteCol.append(optionWebsite);
            groupCol.append(optionGroup);
            groupCol.val(data.group);
            websiteCol.val(data.website_id);
            priceTypeCol.val(data.price_type);
            arguments[6].trigger('change');
            arguments[6].trigger('contentUpdated');

        },
        /**
         * Add selection value for 'select' type of custom option
         */
        addSelection: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                rowTmpl, priceType;

            if (typeof element !== 'undefined') {
                data.id = $(element).closest('#product_options_container_top > div')
                    .find('[name^="product[options]"][name$="[id]"]').val();
                data['option_type_id'] = -1;
                var optionType = $(element).closest('#product_options_container_top > div').find('.select-product-option-type').val();
                var listDrop = ['drop_down', 'radio'];
                data.enableSwatch = 0;
                if ($.inArray(optionType, listDrop) > -1) {
                    data.enableSwatch = 1;
                }



                if (!this.options.selectionItemCount[data.id]) {
                    this.options.selectionItemCount[data.id] = 1;
                }

                data['select_id'] = this.options.selectionItemCount[data.id];
                data.price = data.sku = '';
            } else {
                data = event;
                data.id = data['option_id'];
                data['select_id'] = data['option_type_id'];
                data.enableSwatch = 0;
                this.options.selectionItemCount[data.id] = data['item_count'];
            }

            rowTmpl = this.rowTmpl({
                data: data
            });

            $(rowTmpl).appendTo($('#select_option_type_row_' + data.id));

            //set selected price_type value if set
            if (data['price_type']) {
                priceType = $('#' + this.options.fieldId + '_' + data.id + '_select_' + data['select_id'] +
                    '_price_type');
                priceType.val(data['price_type']).attr('data-store-label', data['price_type']);
            }

            this._bindUseDefault(this.options.fieldId + '_' + data.id + '_select_' + data['select_id'], data);
            this.refreshSortableElements();
            this.options.selectionItemCount[data.id] = parseInt(this.options.selectionItemCount[data.id], 10) + 1;

            $('#' + this.options.fieldId + '_' + data.id + '_select_' + data['select_id'] + '_title').focus();
            if (data.is_default == 1) {
                $('#product_option_'+data.id+'_select_'+data.select_id).find('.admin__actions-switch-label').data('data-check-first-load', 1).click();
            }
            this.dependOptionId($('#' + this.options.fieldId + '_' + data.id + '_select_' + data['select_id'] + '_dependent_id'));
            this.refreshImageUploadElements();
            var self= this;
            setTimeout(function() {
                self.multiselectOption($('#' + self.options.fieldId + '_' + data.id + '_select_' + data['select_id']).find('.dco-button'));
            },1000);
        },

        /**
         * Add custom option
         */
        addOption: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                baseTmpl;
            var checkNewOption = 0;

            if (typeof element !== 'undefined') {
                data.id = this.options.itemCount;
                data.type = '';
                data['option_id'] = 0;
                checkNewOption = 1;

            } else {
                data = event;
                this.options.itemCount = data['item_count'];
            }

            baseTmpl = this.baseTmpl({
                data: data
            });

            $(baseTmpl)
                .appendTo(this.element.find('#product_options_container_top'))
                .find('.collapse').collapsable();

            //set selected type value if set
            if (data.type) {
                $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.type).trigger('change', data);
            }

            //set selected is_require value if set
            if (data['is_require']) {
                $('#' + this.options.fieldId + '_' + data.id + '_is_require').val(data['is_require']).trigger('change');
            }

            if (data['bss_coap_qty']) {
                $('#' + this.options.fieldId + '_' + data.id + '_bss_coap_qty').val(data['bss_coap_qty']).trigger('change');
            }
            if (checkNewOption === 1) {
                var optionElement = $('#option_'+ data.id);
                optionElement.find('.visible-customer-group option').prop('selected', true);
                optionElement.find('.visible-store-view option').prop('selected', true);
                var dataVisibleCustomer = optionElement.find('.visible-customer-group').val();
                var dataVisibleStore = optionElement.find('.visible-store-view').val();
                if (dataVisibleCustomer) {
                    dataVisibleCustomer = dataVisibleCustomer.toString();
                    optionElement.find('input.data-visible-customer').val(dataVisibleCustomer);
                }
                if (dataVisibleStore) {
                    dataVisibleStore = dataVisibleStore.toString();
                    optionElement.find('input.data-visible-store').val(dataVisibleStore);
                }
            }

            this.refreshSortableElements();
            this._bindCheckboxHandlers();
            this._bindCheckboxHandlers2();
            this._bindReadOnlyMode();
            this.options.itemCount++;
            if($("#template_tabs_bss_custom_option").attr("aria-expanded") === 'true') {
                $('#' + this.options.fieldId + '_' + data.id + '_title').trigger('change');
                this.dependOptionId($('#' + this.options.fieldId + '_' + data.id + '_dependent_id'))
            }

        },

        _afterClickReload: function () {
            $("body").on('click', ".image-customoption-container .action-edit",function(){
                $(this).closest('.image-customoption-container').find('.upload-image-coi').trigger('click');
            });
            $('#product_options_container_top').on('click mouseenter',function(){
                setTimeout(function(){
                    $(".image-customoption-container .preview").hover(function(){
                        if ($(this).find('.preview_image').attr('src') !='') {
                            $(this).find('.action-delete').show();
                        }
                    }, function(){
                        $(this).find('.action-delete').hide();
                        $(this).find('.action-edit').hide();
                    });
                    $(".image-customoption-container .action-delete").not('.action-edit').on('click',function(){
                        $(this).parent().find('.preview_image').attr('src','');
                        $(this).parents('.image-customoption-container').find('.image_url').val('');
                        $(this).parents('.image-customoption-container').find('.swatch_image_url').val('');
                        $(this).closest('.image-customoption-container').find('.image-placeholder').show();
                    });

                    $('.preview a').magnificPopup({
                        type: 'image',
                        closeOnContentClick: true,
                        mainClass: 'mfp-img-mobile',
                        image: {
                            verticalFit: true
                        }
                    });
                },300)
            })
        },

        /**
         *
         */
        refreshImageUploadElements: function () {
            var $dropPlaceholder = $('.image-placeholder-customoption');
            $('#product_options_container_top').find('.image-customoption-container input[type="file"]').fileupload({
                dataType: 'json',
                dropZone: $dropPlaceholder.closest('[data-attribute-code]'),
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: 20971520,
                done: function (event, data) {
                    $dropPlaceholder.find('.progress-bar').text('').removeClass('in-progress');
                    if (!data.result) {
                        return;
                    }
                    if (!data.result.error) {
                        $(this).parents('.image-customoption-container').find('.preview a').attr('href',data.result.url);
                        $(this).parents('.image-customoption-container').find('.preview_image').attr('src',data.result.url);
                        $(this).parent().find('.image_url').val(data.result.url);
                        $(this).parent().find('.swatch_image_url').val(data.result.url);
                        $(this).parent().hide();
                    } else {
                        alert({
                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                        });
                    }
                },
                add: function (event, data) {
                    $(this).fileupload('process', data).done(function () {
                        data.submit();
                    });
                },
                start: function (event) {
                    var uploaderContainer = $(event.target).closest('.image-customoption-container');

                    uploaderContainer.addClass('loading');
                },
                stop: function (event) {
                    var uploaderContainer = $(event.target).closest('.image-customoption-container');

                    uploaderContainer.removeClass('loading');
                }
            });
        },

        /**
         * Sync sort order checkbox with hidden dropdown
         */
        _bindCheckboxHandlers2: function () {
            this._on({
                /**
                 * @param {jQuery.Event} event
                 */
                'change [id^=product_option_][id$=_coap_qty]': function (event) {
                    var $this = $(event.target);

                    $this.closest('#product_options_container_top > div')
                        .find('[name$="[bss_coap_qty]"]').val($this.is(':checked') ? 1 : 0);
                }
            });
            this.element.find('[id^=product_option_][id$=_coap_qty]').each(function () {
                $(this).prop('checked', $(this).closest('#product_options_container_top > div')
                    .find('[name$="[bss_coap_qty]"]').val() > 0);
            });
        },

        dependOptionId: function ($this) {
            if ($this.val() == '' || parseInt($this.val()) == '0'){
                var val = Math.floor(Date.now());
                $('#cot-depend-last-increment-id').val(val);
                $this.val(val);
                $this.parent().find('span.bss_dco_span').text(val);
                window.bss_depend_id[val] = true;
                if (window.bss_depend_option[$this.attr('option_key') || $this.closest('tr').find('.dco-button').attr('option_key')] === undefined){
                    window.bss_depend_option[$this.attr('option_key') || $this.closest('tr').find('.dco-button').attr('option_key')] = {};
                }
                window.bss_depend_option[$this.attr('option_key') || $this.closest('tr').find('.dco-button').attr('option_key')][val] = true;
            }
        },

        multiselectOption: function ($this) {
            var currentIds = window.bss_depend_option[$this.attr('option_key')];
            $this.parents('tr').find('.multiselect-dco').find('option').remove();
            if (!$.isEmptyObject(window.bss_depend_id)) {
                var inputSelected = $this.parents('tr').find('.multiselect-dco').parent().find('input').val().split(',');
                var cloneSelected = [];
                $.each(inputSelected, function (index, value) {
                    if (value in window.bss_depend_id) {
                        cloneSelected.push(value);
                    }
                });
                $this.parents('tr').find('.multiselect-dco').parent().find('input').val(cloneSelected.toString());
            }
            $.each(window.bss_depend_id, function (k, id) {
                if (currentIds[k] === undefined && $('input[value="'+k+'"]').length > 0) {
                    $this.parents('tr').find('.multiselect-dco').append(
                        new Option(
                            $('input.dependent-id[value="'+k+'"]').not('#cot-depend-last-increment-id').closest('.determined-location').find('.field-option-title .input-text').val(),
                            k
                        )
                    );
                    var depentSelected = $this.parents('tr').find('.multiselect-dco').parent().find('input').val().split(',');
                    $this.parents('tr').find('.multiselect-dco').val(depentSelected)
                }
            });
            $this.parents('tr').find('.multiselect-dco').addClass('active').show();
            $this.parents('tr').find('.dco-control-input').hide();
        },

        multiselectChange: function (select) {
            var result = [];
            var options = select.find('option');
            var opt;
            if (select.val()) {
                var checkNotSelectSameParent = true;
                var elementRow = select.closest('tr');
                var cloneSelectData = select.val();
                var dependId = elementRow.find('.dependent-id').val();
                var dependIdParent = elementRow.closest('tr').closest('fieldset.fieldset').find('.parent-option-dependent-id').val();
                $.each(select.val(), function (index, value) {
                    if (!checkNotSelectSameParent) {
                        return false;
                    }
                    var optionKey = $('.dependent-id[value="' + value + '"]').attr('option_key');
                    if (window.bss_depend_option[optionKey]) {
                        $.each(window.bss_depend_option[optionKey], function (indexDepend, valueDepend) {
                            if (!checkNotSelectSameParent) {
                                return false;
                            }
                            var element = $('.dependent-id[value="' + indexDepend + '"]');
                            element.closest('tr').find('.multiselect-dco').each(function () {
                                var same = $(this).closest('tr').find('.dependent-id').val();
                                var sameParent = element.closest('tr').closest('fieldset.fieldset').find('.parent-option-dependent-id').val();
                                if (($(this).val() && (value === same || value === sameParent) && (($.inArray(dependId, $(this).val()) > -1) || $.inArray(dependIdParent, $(this).val()) > -1)) === true) {
                                    checkNotSelectSameParent = false;
                                    cloneSelectData.splice(index, 1);
                                    alert({
                                        content: $.mage.__("You can't not select its parent custom option as dependent custom option ")
                                    });
                                    return false;
                                }
                            });
                        })
                    }
                });
                select.val(cloneSelectData);
                return cloneSelectData;
            }
            return '';
        },
    });
    return $.bss.customOptions;
});
