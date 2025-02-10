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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _, mageTemplate, priceUtils, $t) {
    window.cpwd_swatch_renderer = true;

    $.widget('bss.SwatchRenderer', $.mage.SwatchRenderer, {
        _EventListener: function () {
            var $widget = this,
                swatchLength = $('.swatch-opt').length;
            this._super();
            //if have swatch attributes outside wholesale table, auto select first option
            if (swatchLength) {
                customUrl = $(location).attr('href');
                selectedAttr = customUrl.split('+');
                if (selectedAttr.length < 2) {
                    $('.swatch-opt').find('.swatch-option').first().trigger('click');
                }
            }
            $('.bss-ptd-table').on('click','.swatch-option', function () {
                var optionId,
                    productId;
                if ($(this).hasClass('selected') || $(this).closest('.bss-table-row-attr').hasClass('selected')) {
                    $(this).removeAttr('data-option-selected option-selected').removeClass('selected');
                    if (!$widget.options.isEnabledSdcp) {
                        $widget._loadMedia();
                    }
                } else {
                    optionId = $widget.getAttrValue($(this), 'option-id');
                    $('#bss-ptd-table').find('.selected').removeAttr('data-option-selected option-selected').removeClass('selected');
                    $(this).attr({'data-option-selected' : optionId, 'option-selected' : optionId});
                    $(this).addClass('selected');
                    if (!$widget.options.isEnabledSdcp) {
                        productId = $widget.getAttrValue($(this).closest('.bss-table-row').find('.bss-table-row-attr.swatch-option.selected'), 'data-product-id');
                        return $widget._loadProductMedia(productId);
                    }
                }
            });
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $widget.getAttrValue($parent, 'attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput),
                checkAdditionalData = '';
                if (this.options.jsonSwatchConfig[attributeId]['additional_data']) {
                    checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']);
                }
            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr({'data-option-selected' : $widget.getAttrValue($this, 'option-id'), 'option-selected' : $widget.getAttrValue($this, 'option-id')}).find('.selected').removeClass('selected');
                $label.text($widget.getAttrValue($this, 'option-label'));
                $input.val($widget.getAttrValue($this, 'option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    _.findKey($widget.options.jsonConfig.index, $widget.options.jsonConfig.defaultValues),
                    $widget.options.jsonConfig.optionPrices
                ]);

            if ((!window.bssGallerySwitchStrategy || window.bssGallerySwitchStrategy != 'disabled') && checkAdditionalData) {
                if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                    $widget._loadMedia();
                }
            }

            $input.trigger('change');

            if (window.compatible_improved !== undefined) {
                $widget._super($this, $widget);
            }
        },

        _RenderControls: function () {
            var $widget = this;
            this._super();
            if ($('.bss-ptd-table').length) {
                var item_id = 0,
                    product_id = 0,
                    encodeAddCart,
                    addCart,
                    encodeUpdateCart,
                    updateCart,
                    bssConfig = window.checkout.bssConfigurableWholesale;
                if (bssConfig) {
                    item_id = bssConfig.item_id;
                    product_id = bssConfig.product_id;
                }
                if (item_id && product_id) {
                    $.ajax({
                        type: 'post',
                        url: bssConfig.urlLoadItem,
                        data: {product: product_id, item_id: item_id},
                        dataType: 'json',
                        success: function (data) {
                            window.jsonEditInfo = data;
                            $widget._UpdateQty();
                            $('.bss-ptd-table').trigger('reloadTable');
                        },
                        error: function () {

                        }
                    });
                }
            }
        },

        _UpdateQty: function () {
            var editInfo = window.jsonEditInfo;
            if (!_.isEmpty(editInfo)) {
                var productId = editInfo.default,
                    items = editInfo.product;
                _.each(items[productId]['data'], function (attributeVal, attribute) {
                    var select,
                        attributeId = attribute.replace('data-option-', '');
                    $('.swatch-attribute[attribute-id='+attributeId+']').find('.swatch-option[option-id='+attributeVal+']').trigger('click');
                    select = $('.swatch-attribute[attribute-id='+attributeId+']').find('.swatch-select [option-id='+attributeVal+']').val();
                    if (select) {
                        $('.swatch-attribute[attribute-id='+attributeId+']').find('.swatch-select').val(select).trigger('change');
                    }
                });
            }
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @private
         */
        _loadProductMedia: function (productId) {
            var $main = this.inProductList ?
                this.element.parents('.product-item-info') :
                this.element.parents('.column.main'),
                images,
                self = this;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            } else {
                if (!productId) {
                    productId = this.getProduct();
                }
                images = this.options.jsonConfig.images[productId];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                setTimeout(function () {
                    self.updateBaseImage(images, $main, !self.inProductList);
                }, 500);
            }
        },
        /**
         * Get attribute value in two case
         * @param $ele
         * @param $attr
         * @returns {*}
         */
        getAttrValue($ele, $attr) {
            if ($ele.attr($attr)) {
                return $ele.attr($attr);
            }
            return $ele.data($attr);
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                result = $widget._getNewPrices(),
                tierPriceHtml,
                isShow;

            if (typeof $productPrice.priceBox !== "undefined") {
                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                    }
                );
            }

            isShow = typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount;

            $product.find(this.options.slyOldPriceSelector)[isShow ? 'show' : 'hide']();

            if (typeof result != 'undefined' && result.tierPrices && result.tierPrices.length) {
                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(
                        this.options.tierPriceTemplate,
                        {
                            'tierPrices': result.tierPrices,
                            '$t': $t,
                            'currencyFormat': this.options.jsonConfig.currencyFormat,
                            'priceUtils': priceUtils
                        }
                    );
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }

            $(this.options.normalPriceLabelSelector).hide();

            _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                    if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                        _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                            if ($(dropdown).val() === '0') {
                                $(this.options.normalPriceLabelSelector).show();
                            }
                        }.bind(this));
                    } else {
                        $(this.options.normalPriceLabelSelector).show();
                    }
                }
            }.bind(this));
        }
    });
    return $.bss.SwatchRenderer;
});
