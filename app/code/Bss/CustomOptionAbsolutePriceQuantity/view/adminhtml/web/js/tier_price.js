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
define([
    'jquery',
    'jquery/ui',
    'Magento_Catalog/js/form/element/input',
    'Magento_Ui/js/form/element/single-checkbox',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/mage',
    "prototype",
    "mage/adminhtml/form"
], function ($, ui, checkbox,singleCheckbox,mageTemplate, alert) {
    'use strict';

    return singleCheckbox.extend({
        defaults: {
            bss_swatch_image: '',
            src: '',
            bss_span: '',
            bss_span_class: '',
            bss_option_so: '',
            bss_value_so: '',
            tempImgSwatch: ''
        },
        initConfig: function () {
            this._super();
            var key1 = this.dataScope.split('.')[3];
            var key2 = this.dataScope.split('.')[5];
            this.bss_swatch_image = 'bss_swatch_image' + key1 + '_' + key2;
            this.bss_span = 'bss_span_' + key1 + '_' + key2;
            this.bss_option_so = this.dataScope.split('.')[3];
            this.bss_value_so = this.dataScope.split('.')[5];
            this.bodyId = 'Bss_absprice_tier' + this.bss_option_so;
            if (this.bss_value_so) {
                this.bodyId += '_' + this.bss_value_so;
            }
            this.bodyId += '_container';
            this.itemsCount = 0;
            var widget = this;
            this.eventCheck(widget);
            return this;
        },
        addItem : function () {
            var optionWebsite ='', optionGroup = '';
            $.each(JSON.parse(this.websites), function (key, value) {
                optionWebsite += '<option value="'+value.value+'">'+value.label+'</option>';
            });
            $.each(JSON.parse(this.customerGroup), function (key, value) {
                optionGroup += '<option value="'+value.value+'">'+value.label+'</option>';
            });
            var tierPriceRowTemplate = '<tr>'
                + '<td class="col-websites">'
                + '<select class="required-entry inputValue admin__control-select" data-check-type="website_id" name="'+this.inputName+'[<%- data.index %>][website_id]" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_website">'+
                optionWebsite
                + '</select></td>'
                + '<td class="col-customer-group"><select class=" custgroup required-entry inputValue admin__control-select" data-check-type="cust_group" name="'+this.inputName+'[<%- data.index %>][cust_group]" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_cust_group">'
                + optionGroup
                + '</select></td>'
                + '<td class="col-qty">'
                + '<input class="qty required-entry validate-greater-than-zero validate-number inputValue admin__control-text" data-check-type="price_qty" type="text" name="'+this.inputName+'[<%- data.index %>][price_qty]" value="<%- data.qty %>" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_qty" />'
                + '</td>'
                + '<td class="col-price control-grouped admin__control-fields">' +
                '<div class="admin__field-control control-grouped admin__control-fields">'+
                '<div class="admin__field">'+
                '<select class=" price-type required-entry inputValue admin__control-select"  data-check-type="price-type" name="'+this.inputName+'[<%- data.index %>][price-type]" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_price_type">'
                + '<option value="fixed">Fixed</option>'
                + '<option value="percent">Discount</option>'
                + '</select></div>' +
                '<div class="admin__field">'+
                '<label class="admin__addon-prefix bss-abs-symbol"> <span>'+'<%- data.symbol %>'+'</span> </label>' +
                '<input class="required-entry bss-abs-price-input validate-greater-than-zero validate-number inputValue admin__control-text" type="text"  data-check-type="price" name="'+this.inputName+'[<%- data.index %>][price]" value="<%- data.price %>" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_price" /></div></div></td>'
                + '<td class="col-delete"><input type="hidden" name="'+this.inputName+'[<%- data.index %>][delete]" class="delete" value="" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_delete" />'
                + '<button title="Delete Tier" type="button" class="action-delete option-tier-price icon-btn delete-product-option" id="option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so+'<%- data.index %>_delete_button" data-bind="click: deleteItem">'
                + '<span>'+$.mage.__('Delete')+'</span></button></td>'
                + '</tr>';
            var progressTmpl = mageTemplate(tierPriceRowTemplate),
                tmpl;
            var data = {
                website_id: this.defaultWebsite,
                group: this.defaultCustomerGroup,
                qty: '',
                price: '',
                price_type: 'fixed',
                readOnly: false,
                index: this.itemsCount++,
                symbol: this.currencySymbol
            };
            if (arguments.length > 4) {
                data.website_id = arguments[0];
                data.group      = arguments[1];
                data.qty        = arguments[2];
                data.price_type = arguments[3];
                data.price      = arguments[4];
                if (data.price_type ==='fixed') {
                    data.symbol = this.currencySymbol;
                } else {
                    data.symbol = "%";
                }
            }
            tmpl = progressTmpl({
                data: {
                    website_id: data.website_id,
                    group: data.group,
                    qty: data.qty,
                    price :data.price,
                    price_type: data.price_type,
                    index :data.index,
                    symbol : data.symbol
                }
            });
            $('#'+this.bodyId+' table tbody').append(tmpl);
            $('#option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so + data.index + '_cust_group').val(data.group);
            $('#option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so + data.index + '_website').val(data.website_id);
            $('#option_tier_price_row_' + this.bss_option_so + '_' + this.bss_value_so + data.index + '_price_type').val(data.price_type);
            $('#'+this.bodyId+' table tbody').trigger('change');
            $('#'+this.bodyId+' table tbody').trigger('contentUpdated');
        },
        clickAddTier: function () {
            this.addItem();
        },
        checkandAddData: function ($widget, $this) {
            var dataForm = $($this).parents('.option-tier-price-div').find('.bss-custom-option-tier-price-form');
            dataForm.validation();
            dataForm.mage('validation', {});
            if (dataForm.validation('isValid')) {
                var data = [];
                var checkNotDupliate = true;
                $($this).parents('.option-tier-price-div').find('aside table tbody tr').map(function (index, elem) {
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
                    var element = $($this).parentsUntil('.option-tier-price-div').find('.ahii');
                    element.val(JSON.stringify(data));
                    element.trigger('change');
                    $($this).parents('.option-tier-price-div').find('aside').removeClass('_show');
                    $($this).parentsUntil('.option-tier-price-div').find('table').remove();
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
        eventCheck: function ($widget) {
            $("body").on("click", '.option-tier-price-div:has(#'+$widget.bodyId+') .add-tier-price-row', function(event){
                event.stopImmediatePropagation();
                $widget.addItem();
                return false;
            });
            $("body").on("click", '.option-tier-price-div:has(#'+$widget.bodyId+') .action-delete.option-tier-price', function(event){
                event.stopImmediatePropagation();
                $(this).parentsUntil('tr').parent().remove();
                return false;
            });
            $("body").on("click", '.option-tier-price-div:has(#'+$widget.bodyId+')  #add-tier-price-button', function(event){
                event.stopImmediatePropagation();
                var tierPriceTableTemplate = '<table class="admin__control-table tiers_table" id="tiers_table">' +
                    '                                        <thead>' +
                    '                                        <tr>' +
                    '                                            <th class="col-websites">'+$.mage.__('Website')+'</th>' +
                    '                                            <th class="col-customer-group">'+$.mage.__('Customer Group')+'</th>' +
                    '                                            <th class="col-qty required" >'+$.mage.__('Quantity')+'</th>' +
                    '                                            <th class="col-price required">'+$.mage.__('Price')+'</th>' +
                    '                                            <th class="col-delete">'+$.mage.__('Action')+'</th>' +
                    '                                        </tr>' +
                    '                                        </thead>' +
                    '                                        <tbody></tbody>' +
                    '                                        <tfoot>' +
                    '                                        <tr>' +
                    '                                            <td colspan="5">' +
                    '                                                <button type="button" class="add-tier-price-row" data-bind="event: { click: clickAddTier }">' +
                    '                                                    <span>'+$.mage.__('Add')+'</span>' +
                    '                                                </button>' +
                    '                                            </td>' +
                    '                                        </tr>' +
                    '                                        </tfoot>' +
                    '                                    </table>';
                $(this).parent().find('.bss-custom-option-tier-price-form').append(tierPriceTableTemplate);
                $(this).parent().find('.bss-custom-option-tier-price-form').trigger('contentUpdated');
                var element = $(this).parent().find('.ahii');
                if (element.val()) {
                    $.each(JSON.parse(element.val()), function (key, value) {
                        $widget.addItem(value['website_id'],value['cust_group'],value['price_qty'],value['price-type'],value['price']);
                    });
                }
                $(this).parent().find('aside').addClass('_show');
                return false;
            });
            $("body").on("click", '.option-tier-price-div:has(#'+$widget.bodyId+') .action-close', function(event){
                event.stopImmediatePropagation();
                $widget.checkandAddData($widget, this);
            });
            $("body").on("click", '.option-tier-price-div:has(#'+$widget.bodyId+') .action-primary', function(event){
                event.stopImmediatePropagation();
                $widget.checkandAddData($widget, this);
            });
            $("body").on("change", '.option-tier-price-div:has(#'+$widget.bodyId+') .price-type', function(event){
                event.stopImmediatePropagation();
                if ($(this).val() === 'fixed') {
                    $(this).parent().parent().find('.bss-abs-symbol span').text($widget.currencySymbol);
                } else {
                    $(this).parent().parent().find('.bss-abs-symbol span').text('%');
                }
            });
        },
    });
});
