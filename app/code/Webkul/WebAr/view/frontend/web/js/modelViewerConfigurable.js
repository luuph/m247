/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    // This is removed to make this module compatible with QuickProductEdit module and added CDN on phtml
    // 'Webkul_WebAr/js/webxr/mv/model-viewer.min'
], function ($, umd) {
    'use strict';
    $.widget('modelViewerConfigurable.webar', {
        options: {},

        _create: function () {
            var self = this;
            var productType = self.options.productType;
            var performInterpolationDecay = self.options.performInterpolationDecay;
            var showSeperateGlb = self.options.showSeperateGlb;
            
            const modelViewerVariants = document.querySelector("model-viewer");

            ////Trigger Variant Selector////
            $(document).ready(function() {
                const customOptionSelect = $('.product-options-wrapper select[id^="attribute"]');
                if (customOptionSelect != null) {
                    customOptionSelect.click(function() {
                        setTimeout(() => {
                            var modelViewer = document.querySelector("model-viewer");
                            if (modelViewer) {
                                self.setModelVariant(modelViewer, "select", $(this));
                            }
                        }, 200);
                    });
                }
                if ($('.swatch-opt').length > 0) {
                    $('body').on('click', '.swatch-option', function(e) {
                        setTimeout(() => {
                            var modelViewer = document.querySelector("model-viewer");
                            if (modelViewer) {
                                self.setModelVariant(modelViewer, "swatch", $(this));
                            }
                        }, 200);
                    });
                }
            });
            /////////

            if (modelViewerVariants) {
                modelViewerVariants.addEventListener("load", (ev) => {
                    if (productType == 'configurable') {
                        if ($('.swatch-opt').length > 0) {
                            $('body').on('click', '.swatch-option', function(e) {
                                var swatchid = $('.swatch-attribute').attr('data-option-selected');
                                if (swatchid) {
                                    var label = $(".swatch-option[data-option-id='"+swatchid+"']").attr('data-option-label');
                                    modelViewerVariants.variantName = label;
                                }  
                            });
                        } else {
                            const attributeSelect = document.querySelector(
                                '.product-options-wrapper select[id^="attribute"]'
                            );
                            attributeSelect.addEventListener('input', (event) => {
                                self.setModelVariant(modelViewerVariants, "select");
                            });
                        }
                    }

                    /////Set GLB and USDZ files if showSeperateGlb is true///
                    $(document).on("click",".wk-slide",function() {
                        if (showSeperateGlb && productType == 'configurable'
                            && $(this).attr("data-model") != "") {
                            modelViewerVariants.src = $(this).attr("data-model");
                            modelViewerVariants.iosSrc = $(this).attr("data-ios-model");
                            modelViewerVariants.poster = $(this).attr("data-thumb");
                        }
                    });
                    /////
                });
            }

            ////Process Interpolation-Decay////
            if (parseInt(performInterpolationDecay) == 1) {
                setTimeout(() => {
                    var modelViewer = document.querySelector("model-viewer");
                    self.processInterpolationDecay(modelViewer);
                }, 500);
            }
            /////////////////////////////////
        },

        /**
         * Process Interpolation Decay in AR Model
         * 
         * @param {Element} modelViewerVariants 
         */
        processInterpolationDecay: function (modelViewerVariants) {
            if (modelViewerVariants) {
               
                const orbitCycle = [
                    '45deg 55deg 4m',
                    '-60deg 110deg 2m',
                    '0deg 0deg 0m',
                    modelViewerVariants.cameraOrbit
                ];
                
                setInterval(() => {
                    const currentOrbitIndex = orbitCycle.indexOf(modelViewerVariants.cameraOrbit);
                    modelViewerVariants.cameraOrbit =
                        orbitCycle[(currentOrbitIndex + 1) % orbitCycle.length];
                }, 3000);
            }
        },

        /**
         * Set Model Variant
         * 
         * @param {Element} modelViewerVariants
         * @param {String} swatchType
         * @param {Element} currentElement
         */
        setModelVariant: function(modelViewerVariants, swatchType = "select", currentElement = "") {
            var self = this;
            var optionText = "";
            var optionTextArr = []; 
            
            var selectedText = "";
            var selectedVal = "";
            var id = "";

            var selectedSwatchAttrId = 0;
            var defaultVariantAttribute = self.options.defaultVariantAttribute;
            
            if (swatchType == "select") {
                id = "#attribute"+defaultVariantAttribute;
                selectedText = $(id+' option:selected').text();
            } else {
                if ($('.swatch-attribute-options .swatch-option[aria-checked="true"]').length) {
                    var swatchId = "";
                    var idParts = [];
                    $('.swatch-attribute-options .swatch-option[aria-checked="true"]').each(function() {
                        swatchId = $(this).attr("id");
                        idParts = swatchId.split('-');

                        for (let index=0; index<idParts.length; index++) {
                            if ($.isNumeric(idParts[index])) {
                                selectedSwatchAttrId = idParts[index];
                                break;
                            }
                        }
                        if (parseInt(defaultVariantAttribute) == selectedSwatchAttrId) {
                            selectedText = $(this).attr("data-option-label");
                        }
                    });
                }
            }

            /////Get Selected Variant Value/////
            id = "#attribute"+defaultVariantAttribute;
            selectedVal = $(id+' option:selected').val();
            if (selectedVal == "" && swatchType == "select") {
                selectedVal = $(id+' option:eq(1)').text();
            } else {
                selectedVal = selectedText;
            }
            /////

            if (swatchType == "select") {
                if (selectedText.indexOf('+') == -1) {
                    optionText = selectedText;
                } else {
                    optionTextArr =  selectedText.split('+');
                    optionText = $.trim(optionTextArr[0]);
                }    
            } else {
                optionText = selectedVal;
            }

            if (optionText.indexOf('"')) {
                let optionTextNewArr =  optionText.split('"');
                optionText = $.trim(optionTextNewArr[0]);
            }
          
            modelViewerVariants.variantName = optionText;

            setTimeout(function() {
                let selectedProduct = $("input[name='selected_configurable_option']").val();

                //If showSeperateGlb is true, set AR Model based on selected variant//
                if (self.options.showSeperateGlb) {
                    var associatedProducts = self.options.associatedProducts;
                    if (!selectedVal && selectedProduct) {
                        selectedProduct = self.options.firstAssociatedProduct;
                    }

                    if (selectedProduct == "" && typeof selectedVal == "undefined") {
                        selectedProduct = self.options.firstAssociatedProduct;
                    }

                    if ((typeof selectedProduct == "undefined" || selectedProduct == "")
                        && typeof $('[data-role="swatch-options"]').data('mage-SwatchRenderer') != "undefined") {
                        selectedProduct = $('[data-role="swatch-options"]').data('mage-SwatchRenderer').getProduct();
                    }
                    
                    if (typeof associatedProducts[selectedProduct] != "undefined") {
                        self.options.modelUrl = associatedProducts[selectedProduct]["modelUrl"];
                        self.options.iosModelUrl = associatedProducts[selectedProduct]["iosModelUrl"];
                    }
                }
                ////
                let variantDivId = $("#wk-slide-"+selectedProduct);
                variantDivId.trigger("click");

            }, 200);
        }
    });
    return $.modelViewerConfigurable.webar;
});
