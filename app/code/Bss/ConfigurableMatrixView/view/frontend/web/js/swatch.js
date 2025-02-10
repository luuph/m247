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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
], function($) {
    "use strict";

    $.widget('bss.swatch', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},
        },

        _init: function () {
            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                this._RenderControls();
                this._EventListener();
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                attr_id = '';

            $widget.optionsMap = {};
            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    label = '';

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                if ($widget.productForm) {
                    $widget.productForm.append(input);
                    input = '';
                }

                // Create new control
                attr_id = container.find('input.swatch-attribute').val();
                if (options[attr_id] == undefined) {
                    return;
                }
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                        '" attribute-code="' + item.code +
                        '" attribute-id="' + item.id + '">' +
                            label +
                        '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options[attr_id] +
                        '</div>' +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });
        },

        _RenderSwatchOptions: function (config) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                obj = {};

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function () {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    html = '',
                    attr;
                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                label = this.label ? this.label : '';
                attr =
                    ' option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        '" style="background: ' + value +
                        ' no-repeat center; background-size: initial;box-shadow: 0px 4px 5px 0' + value + '">' + '' +
                        '</div>';
                    // html += '<span class="label-grv">' + label + '</span>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Default
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
                obj[id]= html;
            });

           return obj;
        },

        _EventListener: function () {
            var $widget = this,
                options = this.options.classes,
                attribute_id,
                option_id;
            // Visual Swatch & Text Swatch
            $widget.element.on('click', '.' + options.optionClass, function () {
                $('.bss-swatch .swatch-option').removeClass('selected');
                $(this).addClass('selected');
                option_id = $(this).attr('option-id') ?? $(this).attr('data-option-id');
                $('.product-swatch-default .' + options.optionClass).each(function() {
                    if ($(this).attr('option-id') == option_id || $(this).attr('data-option-id') == option_id) {
                        $(this).trigger('click');
                    }
                });
            });

            // DropDown
            $widget.element.on('click', '.lable-super-attribute', function () {
                $('.bss-swatch .lable-super-attribute').removeClass('selected');
                $(this).addClass('selected');
                attribute_id = $(this).parent().attr('attribute-id') ?? $(this).attr('data-attribute-id');
                option_id = $(this).parent().attr('attribute-value');
                $('.product-swatch-default .swatch-attribute').each(function() {
                    if ($(this).attr('attribute-id') == attribute_id || $(this).attr('data-attribute-id') == attribute_id) {
                        $(this).find('select').val(option_id).trigger('change');
                    }
                });
                $('.product-swatch-default .super-attribute-select').each(function() {
                    var id = 'attribute' + attribute_id;
                    if ($(this).attr('id') == id) {
                        $(this).val(option_id).trigger('change');
                    }
                });
            });
        }
    });
    return $.bss.swatch;
});
