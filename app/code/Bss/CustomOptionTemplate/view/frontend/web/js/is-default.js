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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'jquery/ui'
], function ($, _) {
    'use strict';
    $.widget('bss.is_default_values', {
        _create: function () {
            var $widget = this;
            $widget.updateEventListener($widget);
        },
        updateEventListener: function ($widget) {
            $(document).ready(function() {
                setTimeout( function(){
                    $widget.setIsDefault();
                }, 500 );
            });

        },
        setIsDefault: function () {
            var defaultData = this.options.isDefaultJson;
            var arrBodyClass = $('body').attr('class').split(' ');
            if (!$.isEmptyObject(this.options.isDefaultJson) && $.inArray('checkout-cart-configure', arrBodyClass) < 0) {
                window.optionsIsDefaultJson = this.options.isDefaultJson;
                $.each(this.options.isDefaultJson, function(optionId) {
                    if (defaultData[optionId].selected) {
                        if ($.inArray(defaultData[optionId].type, ['drop_down', 'multiple']) !== -1) {
                            var elementSelect = $('#select_'+optionId);
                            var selectedValues = [];
                            if ($.inArray(defaultData[optionId].type, ['multiple']) !== -1) {
                                selectedValues = _.values(defaultData[optionId].selected);
                                elementSelect.val(selectedValues).change();
                            } else {
                                elementSelect.val(defaultData[optionId].selected[0]).change();
                            }
                        } else {
                            $.each(defaultData[optionId].selected, function (index, value) {
                                var elementInput = $('[data-selector="options['+optionId+']['+value+']"]');
                                if (defaultData[optionId].type === 'radio') {
                                    elementInput = $('[data-selector="options['+optionId+']"]').filter(function(){return this.value==value});
                                }
                                if (elementInput.closest('.Bss_image_radio').length > 0) {
                                    elementInput.closest('.Bss_image_radio').trigger('click')
                                } else {
                                    elementInput.prop('checked', true);
                                    elementInput.change();
                                }
                            });

                        }
                    }
                });
            }
        }
    });
    return $.bss.is_default_values;
});
