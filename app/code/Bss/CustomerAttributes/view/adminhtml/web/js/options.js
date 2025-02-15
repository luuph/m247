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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'jquery/ui',
    'prototype',
    'form',
    'validation'
], function (jQuery, mageTemplate, rg) {
    'use strict';

    return function (config) {
        var attributeOption = {
                table: $('attribute-options-table'),
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                template: mageTemplate('#row-template'),
                isReadOnly: config.isReadOnly,
                add: function (data, render) {
                    var isNewOption = false,
                        element;

                    if (typeof data.id == 'undefined') {
                        data = {
                            'id': 'option_' + this.itemCount,
                            'sort_order': this.itemCount + 1
                        };
                        isNewOption = true;
                    }

                    if (!data.intype) {
                        data.intype = this.getOptionInputType();
                    }

                    element = this.template({
                        data: data
                    });

                    if (isNewOption && !this.isReadOnly) {
                        this.enableNewOptionDeleteButton(data.id);
                    }
                    this.itemCount++;
                    this.totalItems++;
                    this.elements += element;

                    if (render) {
                        this.render();
                        this.updateItemsCountField();
                    }
                },
                remove: function (event) {
                    var element = $(Event.findElement(event, 'tr')),
                        elementFlags; // !!! Button already have table parent in safari

                    // Safari workaround
                    element.ancestors().each(function (parentItem) {
                        if (parentItem.hasClassName('option-row')) {
                            element = parentItem;
                            throw $break;
                        } else if (parentItem.hasClassName('box')) {
                            throw $break;
                        }
                    });

                    if (element) {
                        elementFlags = element.getElementsByClassName('delete-flag');

                        if (elementFlags[0]) {
                            elementFlags[0].value = 1;
                        }

                        element.addClassName('no-display');
                        element.addClassName('template');
                        element.hide();
                        this.totalItems--;
                        this.updateItemsCountField();
                    }
                },
                updateItemsCountField: function () {
                    $('option-count-check').value = this.totalItems > 0 ? '1' : '';
                },
                enableNewOptionDeleteButton: function (id) {
                    $$('#delete_button_container_' + id + ' button').each(function (button) {
                        button.enable();
                        button.removeClassName('disabled');
                    });
                },
                bindRemoveButtons: function () {
                    jQuery('#swatch-visual-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },
                render: function () {
                    Element.insert($$('[data-role=options-container]')[0], this.elements);
                    this.elements = '';
                },
                renderWithDelay: function (data, from, step, delay) {
                    var arrayLength = data.length,
                        len;

                    for (len = from + step; from < len && from < arrayLength; from++) {
                        this.add(data[from]);
                    }
                    this.render();

                    if (from === arrayLength) {
                        this.updateItemsCountField();
                        this.rendered = 1;
                        jQuery('body').trigger('processStop');

                        return true;
                    }
                    setTimeout(this.renderWithDelay.bind(this, data, from, step, delay), delay);
                },
                ignoreValidate: function () {
                    var ignore = '.ignore-validate input, ' +
                        '.ignore-validate select, ' +
                        '.ignore-validate textarea';

                    jQuery('#edit_form').data('validator').settings.forceIgnore = ignore;
                },
                getOptionInputType: function () {
                    var optionDefaultInputType = 'radio';

                    if ($('frontend_input') && ($('frontend_input').value === 'multiselect' || $('frontend_input').value === 'checkboxs')) {
                        optionDefaultInputType = 'checkbox';
                    }

                    return optionDefaultInputType;
                }
            };

        if ($('add_new_option_button')) {
            Event.observe('add_new_option_button', 'click', attributeOption.add.bind(attributeOption, {}, true));
        }
        $('manage-options-panel').on('click', '.delete-option', function (event) {
            attributeOption.remove(event);
        });
        function b2bShow() {
            jQuery('.field-b2b_account_create').show()
            jQuery('.field-b2b_account_edit').show()
        }
        function b2bHide() {
            jQuery('.field-b2b_account_create').hide()
            jQuery('.field-b2b_account_edit').hide()
        }
        function normalShow() {
            jQuery('.field-customer_account_create_frontend').show()
            jQuery('.field-customer_account_edit_frontend').show()
        }
        function normalHide() {
            jQuery('.field-customer_account_create_frontend').hide()
            jQuery('.field-customer_account_edit_frontend').hide()
        }
        function excuteDisplay(){
            var option = jQuery( ".field-b2b_account_create_backend" ).find(":selected").val();
            if (option == 1){
                normalShow()
                b2bHide()
            } if(option == 2) {
                b2bShow()
                normalHide()
            } if(option == 0) {
                b2bShow()
                normalShow()
            }
        }
        jQuery(document).ready(function(){
            if (jQuery(".field-b2b_account_create_backend" ).length){
                excuteDisplay()
                jQuery(".field-b2b_account_create_backend" ).change(function() {
                    excuteDisplay()
                });
            }
            if (jQuery('#manage-options-panel').length){
                attributeOption.ignoreValidate();

                if (attributeOption.rendered) {
                    return false;
                }
                jQuery('body').trigger('processStart');
                attributeOption.renderWithDelay(config.attributesData, 0, 100, 300);
                attributeOption.bindRemoveButtons();
            }
        });

        if (config.isSortable) {
            jQuery(function ($) {
                $('[data-role=options-container]').sortable({
                    distance: 8,
                    tolerance: 'pointer',
                    cancel: 'input, button',
                    axis: 'y',
                    update: function () {
                        $('[data-role=options-container] [data-role=order]').each(function (index, element) {
                            $(element).val(index + 1);
                        });
                    }
                });
            });
        }

        window.attributeOption = attributeOption;
        window.optionDefaultInputType = attributeOption.getOptionInputType();

        rg.set('manage-options-panel', attributeOption);
    };
});
