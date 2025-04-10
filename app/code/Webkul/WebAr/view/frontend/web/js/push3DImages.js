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
    "jquery"
], function ($) {
    'use strict';
    $.widget('push3DImages.webar', {
        options: {},
        
        _create: function () {
            var self = this;
            let i = 0; //variable to check status for modelViewer click trigger in media gallery case
            var showCustomImageInThumbnail = self.options.showCustomImageInThumbnail;
            var modelThumbnailImg = self.options.mainImageData;
            var associatedProducts = self.options.associatedProducts;
            var showSeperateGlb = self.options.showSeperateGlb;
            var defaultVariantAttribute = self.options.defaultVariantAttribute;

            if (showCustomImageInThumbnail && self.options.attributeConfigs.model_thumbnail != '') {
                modelThumbnailImg = self.options.attributeConfigs.model_thumbnail;
            }

            /////Push 3D Model Images in Product's media gallery////////
            function push3Dimage() {
                var divFotorama = $('div.gallery-placeholder > div.fotorama');
                var fotorama = divFotorama.data('fotorama');

                ///Get Selected Variant Value/////
                var variantValue = self.getSelectedVariantValue();
                
                divFotorama.on('fotorama:load', function fotorama_onLoad(e, fotorama, extra) {
                    if (extra.frame.type === 'ARModel' && extra.frame.src != "") {
                        var viewerHtml = '';
                        viewerHtml += '<model-viewer id="wk3dimage" ';
                        viewerHtml += 'data-js-focus-visible ';
                        viewerHtml += 'src="'+self.options.modelUrl+'" ';
                        viewerHtml += 'alt="A 3D model of an product" camera-controls  ';
                        viewerHtml += ' camera-orbit="33deg 67deg auto" ';

                        if (self.options.productHasOptions) {
                            viewerHtml += 'ar ar-modes="webxr quick-look" ';
                        } else {
                            viewerHtml += 'ar ar-modes="scene-viewer webxr quick-look" ';
                        }

                        ///Set Variant///
                        if (variantValue != '') {
                            viewerHtml += ' variant-name="'+variantValue+'" ';
                        }
                        /////////////////
                        
                        ///Set Dynamic values in attributes////
                        if (self.options.attributeConfigs.auto_rotate == 1) {
                            viewerHtml += ' auto-rotate ';
                            if (self.options.attributeConfigs.auto_rotate_delay != '') {
                                viewerHtml += ' auto-rotate-delay="'+self.options.attributeConfigs.auto_rotate_delay+'" ';
                            }
                        }
                        if (self.options.attributeConfigs.disable_zoom == 1) {
                            viewerHtml += ' disable-zoom ';
                        }
                        if (self.options.attributeConfigs.disable_tap == 1) {
                            viewerHtml += ' disable-tap ';
                        }
                        if (self.options.attributeConfigs.touch_action != '') {
                            viewerHtml += ' touch-action="'+self.options.attributeConfigs.touch_action+'" ';
                        }
                        if (self.options.attributeConfigs.interpolation_decay != '') {
                            viewerHtml += ' interpolation-decay="'+self.options.attributeConfigs.interpolation_decay+'" ';
                        }

                        if (self.options.attributeConfigs.shadow_intensity != '-1') {
                            viewerHtml += ' shadow-intensity="'+self.options.attributeConfigs.shadow_intensity+'" ';
                        }
                        if (self.options.attributeConfigs.exposure != '-1') {
                            viewerHtml += ' exposure="'+self.options.attributeConfigs.exposure+'" ';
                        }
                        if (self.options.attributeConfigs.shadow_softness != '-1') {
                            viewerHtml += ' shadow-softness="'+self.options.attributeConfigs.shadow_softness+'" ';
                        }
                        if (self.options.attributeConfigs.apply_environment_image == 1) {
                            if (self.options.attributeConfigs.environment_image != '' && self.options.attributeConfigs.environment_image != 'envurl') {
                                viewerHtml += ' environment-image="'+self.options.attributeConfigs.environment_image+'" ';
                            } else if(self.options.attributeConfigs.environment_image == 'envurl' 
                                && self.options.attributeConfigs.environment_image_url != '') {
                                    viewerHtml += ' environment-image="'+self.options.attributeConfigs.environment_image_url+'" ';
                            }
                        }
                       
                        if (self.options.attributeConfigs.skybox_image != ''
                            && self.options.attributeConfigs.apply_skybox_image != 0) {
                            viewerHtml += ' skybox-image="'+self.options.attributeConfigs.skybox_image+'" ';
                        }
                        
                        if (self.options.attributeConfigs.loading != '') {
                            viewerHtml += ' loading="'+self.options.attributeConfigs.loading+'" ';
                        }
                        if (self.options.attributeConfigs.poster != '') {
                            viewerHtml += ' poster="'+self.options.attributeConfigs.poster+'" ';
                        }

                        if (self.options.iosModelUrl != "") {
                            viewerHtml += ' ios-src="'+self.options.iosModelUrl+'" ';
                        }
                        ///////////
                        viewerHtml += '>';
                        ////Show Custom AR Button////
                        if (self.options.showCustomButton) {
                            viewerHtml += '<button slot="ar-button" id="ar-button">';
                            viewerHtml += self.options.showCustomButtonText;
                            viewerHtml += '</button>';
                        }
                        //////////////////////////

                        ////Show Associated Products's GLB Models-START////
                        if (associatedProducts.length > 0 && showSeperateGlb) {
                            let thumbNail = "";
                            
                            $.each(associatedProducts, function(assProId, assPro) {
                                thumbNail = assPro["thumbnail"];
                                
                                if (self.options.attributeConfigs.poster != '' && thumbNail == "") {
                                    thumbNail = self.options.attributeConfigs.poster;
                                }
                               
                                viewerHtml += '<div id="wk-slide-'+assProId+'" ';
                                viewerHtml += 'class="wk-slide" ';
                                viewerHtml += 'data-model="'+assPro["modelUrl"]+'" ';
                                viewerHtml += 'data-thumb="'+thumbNail+'" ';
                                viewerHtml += 'data-ios-model="'+assPro["iosModelUrl"]+'" ';
                                viewerHtml += 'data-product-id="'+assProId+'" ';
                                viewerHtml += '</div>';
                            });
                        }
                        /////Show Associated Products's GLB Models-ENDS/////

                        viewerHtml += '</model-viewer> ';
    
                        extra.frame.$stageFrame.html(viewerHtml);
                    }
                });
               
                if (typeof fotorama != "undefined" && (self.options.modelUrl != "" || self.options.modelUrl != null)) {
                    fotorama.unshift({
                        thumb: modelThumbnailImg,
                        'src': self.options.modelUrl,
                        type: 'ARModel',
                        caption: 'AR-Image',
                        isMain: "true",
                        position: 0
                    });
                }
                divFotorama.on('fotorama:showend', function (e, fotorama) {
                    if (fotorama.activeIndex === 0) {
                        fotorama.cancelFullScreen();
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        e.stopPropagation();
                        fotorama.cancelFullScreen();
                        $('document').find('.fotorama__fullscreen-icon').css('display', 'none');
                    }
                });
            }

            ///Functions to handle model viewer's GLB rendering after swiping media gallery images
            function triggerModelViewer(modelViewer, fotorama) {
                if (typeof fotorama != "undefined") {
                    if (fotorama.activeIndex != 0) {
                        i = 0;
                    }
                    if (fotorama.activeIndex == 0 && i == 0) {
                        //re-initialize the fotorama gallery plugin//
                        $('.fotorama').fotorama();
                        /////////////
                        i++;
                    } else if(fotorama.activeIndex != 0) {
                        i = 0;
                    }
                }
            }
           
            function displayModelViewer() {
                var divFotorama = $('div.gallery-placeholder > div.fotorama');
                var fotorama = divFotorama.data('fotorama');
                var modelViewer = $("model-viewer");
        
                if (typeof fotorama != "undefined") {
                    triggerModelViewer(modelViewer, fotorama);
                }
            }
            //////

            //Set 3D model on product media gallery load/////
            $(document).on('gallery:loaded', function () {
                if ((self.options.modelUrl != "" && self.options.modelUrl != null) 
                    && self.options.displayModel && self.options.productType != "configurable"
                ) {
                    push3Dimage();
                }
                //Call displayModelViewer function to trigger model-viewer click
                setInterval(displayModelViewer, 1000);
            });
            ///////
        },

        /**
         * Get Selected Variant Value
         * 
         * @return {string}
         */
        getSelectedVariantValue: function() {
            var optionTextVal = "";
            var optionTextArr = [];
            var selectedText = "";
            var selectedVal = "";

            if ($('.product-options-wrapper select[id^="attribute"]').find().length) {
                selectedText = $('.product-options-wrapper select[id^="attribute"] option:selected').text();
            } else {
                selectedText = $('.swatch-attribute-options .swatch-option[aria-checked="true"]:first').attr("data-option-label");
            }

            /////Get Selected Variant Value/////
            selectedVal = $('.product-options-wrapper select[id^="attribute"] option:selected').val();
            if (selectedVal == "" && $('.product-options-wrapper select[id^="attribute"]').find().length) {
                selectedText = $('.product-options-wrapper select[id^="attribute"] option:eq(1)').text();
            } else {
                selectedVal = selectedText;
            }
            /////

            if ($('.product-options-wrapper select[id^="attribute"]').find().length) {
                if (selectedText.indexOf('+') == -1) {
                    optionTextVal = selectedText;
                } else {
                    optionTextArr =  selectedText.split('+');
                    optionTextVal = $.trim(optionTextArr[0]);
                }    
            } else {
                optionTextVal = selectedVal;
            }
            return optionTextVal;
        }
    });
    return $.push3DImages.webar;
});
