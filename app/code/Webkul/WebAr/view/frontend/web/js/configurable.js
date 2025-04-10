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
    'jquery',
    'underscore',
    'mage/translate'
], function ($, _, $t) {
    'use strict';

    return function (configurable) {
        
        $.widget('mage.configurable', configurable, {
            /**
             * Change displayed product image according to chosen options of configurable product
             *
             * @private
             */
            _changeProductImage: function () {
                var self = this;
                var webArConfig = self.options.spConfig.webArConfig;

                ///Check if 3D Model Display is Enabled
                if (!webArConfig.displayModel) {
                    return self._super();
                }
                ///Check if 'Media Gallery Display with 3D Model' is Enabled
                if (!webArConfig.canShowOtherImages) {
                    return self._super();
                }
                //Check if product have 3D model or not
                if (webArConfig.modelUrl == "" || webArConfig.modelUrl == null) {
                    return self._super();
                }

                var showCustomImageInThumbnail = webArConfig.showCustomImageInThumbnail;
                var modelThumbnailImg = webArConfig.mainImageData;
                
                if (showCustomImageInThumbnail 
                    && webArConfig.attributeConfigs.model_thumbnail != '') {
                    modelThumbnailImg = webArConfig.attributeConfigs.model_thumbnail;
                }

                var images,
                    initialImages = this.options.mediaGalleryInitial,
                    gallery = $(this.options.mediaGallerySelector).data('gallery');

                /////////Load Model Viewer/////
                if (webArConfig.showSeperateGlb) {
                    self._getSelectedVariantValue();
                }
                self._load3DModel();
                //////////////////////////////

                if (_.isUndefined(gallery)) {
                    $(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                        this._changeProductImage();
                    }.bind(this));

                    return;
                }

                images = this.options.spConfig.images[this.simpleProduct];
                
                if (images) {
                    //Remove Extra AR Models
                    initialImages = self._removeExtraARModelfromArray(initialImages);
                    images = self._removeExtraARModelfromArray(images);
                    
                    //Push AR Model in images
                    self._pushARModel(images, modelThumbnailImg);
                    //////////////////

                    images = this._sortImages(images);

                    if (this.options.gallerySwitchStrategy === 'prepend') {
                        images = images.concat(initialImages);
                    }

                    images = $.extend(true, [], images);
                    images = this._setImageIndex(images);

                    gallery.updateData(images);
                    this._addFotoramaVideoEvents(false);
                } else {
                    //Remove Extra AR Models
                    initialImages = self._removeExtraARModelfromArray(initialImages);

                    //Push AR Model in initialImages
                    self._pushARModel(initialImages, modelThumbnailImg);

                    gallery.updateData(initialImages);
                    this._addFotoramaVideoEvents(true);
                }
            },

            /**
             * Set correct indexes for image set.
             *
             * @param {Array} images
             * @private
             */
            _setImageIndex: function (images) {
                var webArConfig = this.options.spConfig.webArConfig;
                ///Check if 3D Model Display is Enabled
                if (!webArConfig.displayModel) {
                    return this._super(images);
                }
                ///Check if 'Media Gallery Display with 3D Model' is Enabled
                if (!webArConfig.canShowOtherImages) {
                    return this._super(images);
                }

                 //Check if product have 3D model or not
                if (webArConfig.modelUrl == "" || webArConfig.modelUrl == null) {
                    return this._super(images);
                }

                var length = images.length,
                    i, j = 1;
                
                for (i = 0; length > i; i++) {
                    if (images[i].type == "ARModel") {
                        images[i].i = 1;
                    } else {
                        images[i].i = j + 1;
                    }
                }
                return images;
            },

            /**
             * Delete Additional AR Model from array
             * 
             * @param {Array} imagesArray
             * @returns {Array}
             * @private
             */
            _removeExtraARModelfromArray: function(imagesArray) {
                imagesArray = imagesArray.filter(element => element.type !== "ARModel");
                return imagesArray;
            },

            /**
             * Push AR Model in array
             * 
             * @param {Array} imagesArray
             * @param {string} modelThumbnailImg
             * @private
             */
            _pushARModel: function(imagesArray, modelThumbnailImg) {
                var self = this;
                var webArConfig = self.options.spConfig.webArConfig;
                var associatedProductsThumbs = webArConfig.associatedProductThumbs;

                //Check if simpleProduct is valid and showCustomImageInThumbnail is false
                if (typeof self.simpleProduct != "undefined" && !webArConfig.showCustomImageInThumbnail) {
                    modelThumbnailImg = associatedProductsThumbs[self.simpleProduct];
                }

                if (typeof imagesArray != "undefined" && 
                (webArConfig.modelUrl != "" || webArConfig.modelUrl != null)) {
                    imagesArray.unshift({
                        thumb: modelThumbnailImg,
                        'src': webArConfig.modelUrl,
                        type: 'ARModel',
                        caption: 'AR-Image',
                        isMain: "true",
                        position: 0
                    });
                }
            },

            /**
             * Get Selected Variant Value
             * 
             * @return {string}
             * @private
             */
            _getSelectedVariantValue: function() {
                var self = this;
                var webArConfig = self.options.spConfig.webArConfig;
                var optionTextVal = "";
                var optionTextArr = []; 

                var selectedText = "";
                var selectedVal = "";

                var defaultVariantAttribute = webArConfig.defaultVariantAttribute;
                var selectId = "";
                
                if (defaultVariantAttribute != "" && defaultVariantAttribute != "0") {
                    selectId = "#attribute"+defaultVariantAttribute;
                }
                
                if (selectId != "") {
                    selectedText = $(selectId+' option:selected').text();
                } else {
                    selectedText = $('.product-options-wrapper select[id^="attribute"] option:selected').text();
                }
                
                /////Get Selected Variant Value/////
                if (selectId != "") {
                    selectedVal = $(selectId+' option:selected').val();
                    if (selectedVal == "") {
                        selectedText = $(selectId+' option:eq(1)').text();
                    } else {
                        selectedVal = selectedText;
                    }
                } else {
                    selectedVal = $('.product-options-wrapper select[id^="attribute"] option:selected').val();
                    if (selectedVal == "") {
                        selectedText = $('.product-options-wrapper select[id^="attribute"] option:eq(1)').text();
                    } else {
                        selectedVal = selectedText;
                    }
                }
                /////
                
                if (selectedText.indexOf('+') == -1) {
                    optionTextVal = selectedText;
                } else {
                    optionTextArr =  selectedText.split('+');
                    optionTextVal = $.trim(optionTextArr[0]);
                }    
              
                //If showSeperateGlb is true then set AR Model based on selected variant//
                if (webArConfig.showSeperateGlb) {
                    var associatedProducts = webArConfig.associatedProducts;
                    var selectedProduct = $("input[name='selected_configurable_option']").val();
                    if (!selectedVal && selectedProduct) {
                        selectedProduct = webArConfig.firstAssociatedProduct;
                    }

                    if (typeof associatedProducts[selectedProduct] != "undefined") {
                        webArConfig.modelUrl = associatedProducts[selectedProduct]["modelUrl"];
                        webArConfig.iosModelUrl = associatedProducts[selectedProduct]["iosModelUrl"];
                    }
                }
                ////
                
                return optionTextVal;
            },

            /**
             * Load 3D Model Viewer
             * 
             * @private
             */
            _load3DModel: function() {
                var self = this;
                var webArConfig = self.options.spConfig.webArConfig;
                var divFotorama = $('div.gallery-placeholder > div.fotorama');

                var associatedProducts = webArConfig.associatedProducts;
                var showSeperateGlb = webArConfig.showSeperateGlb;

                var optionText = "";
                //Get Selected Variant Value
                optionText = self._getSelectedVariantValue();

                if (webArConfig.modelUrl == "" && showSeperateGlb) {
                    return;
                }
               
                divFotorama.on('fotorama:load', function fotorama_onLoad(e, fotorama, extra) {

                    if (extra.frame.type === 'ARModel' && extra.frame.src != "") {
                        var viewerHtml = '';
                        viewerHtml += '<model-viewer id="wk3dimage" ';
                        viewerHtml += 'data-js-focus-visible ';
                        viewerHtml += 'src="'+webArConfig.modelUrl+'" ';
                        viewerHtml += 'alt="A 3D model of an product" camera-controls  ';
                        viewerHtml += ' camera-orbit="33deg 67deg auto" ';

                        if (webArConfig.productHasOptions) {
                            viewerHtml += 'ar ar-modes="webxr quick-look" ';
                        } else {
                            viewerHtml += 'ar ar-modes="scene-viewer webxr quick-look" ';
                        }
                        
                        ///Set Variant///
                        if (optionText != '') {
                            viewerHtml += ' variant-name="'+optionText+'" ';
                        }
                        /////////////////

                        ///Set Dynamic values in attributes////
                        if (webArConfig.attributeConfigs.auto_rotate == 1) {
                            viewerHtml += ' auto-rotate ';
                            if (webArConfig.attributeConfigs.auto_rotate_delay != '') {
                                viewerHtml += ' auto-rotate-delay="'+webArConfig.attributeConfigs.auto_rotate_delay+'" ';
                            }
                        }
                        if (webArConfig.attributeConfigs.disable_zoom == 1) {
                            viewerHtml += ' disable-zoom ';
                        }
                        if (webArConfig.attributeConfigs.disable_tap == 1) {
                            viewerHtml += ' disable-tap ';
                        }
                        if (webArConfig.attributeConfigs.touch_action != '') {
                            viewerHtml += ' touch-action="'+webArConfig.attributeConfigs.touch_action+'" ';
                        }
                        if (webArConfig.attributeConfigs.interpolation_decay != '') {
                            viewerHtml += ' interpolation-decay="'+webArConfig.attributeConfigs.interpolation_decay+'" ';
                        }

                        if (webArConfig.attributeConfigs.shadow_intensity != '-1') {
                            viewerHtml += ' shadow-intensity="'+webArConfig.attributeConfigs.shadow_intensity+'" ';
                        }
                        if (webArConfig.attributeConfigs.exposure != '-1') {
                            viewerHtml += ' exposure="'+webArConfig.attributeConfigs.exposure+'" ';
                        }
                        if (webArConfig.attributeConfigs.shadow_softness != '-1') {
                            viewerHtml += ' shadow-softness="'+webArConfig.attributeConfigs.shadow_softness+'" ';
                        }
                        if (webArConfig.attributeConfigs.apply_environment_image == 1) {
                            if (webArConfig.attributeConfigs.environment_image != '' && webArConfig.attributeConfigs.environment_image != 'envurl') {
                                viewerHtml += ' environment-image="'+webArConfig.attributeConfigs.environment_image+'" ';
                            } else if(webArConfig.attributeConfigs.environment_image == 'envurl' 
                                && webArConfig.attributeConfigs.environment_image_url != '') {
                                    viewerHtml += ' environment-image="'+webArConfig.attributeConfigs.environment_image_url+'" ';
                            }
                        }
                       
                        if (webArConfig.attributeConfigs.skybox_image != ''
                            && webArConfig.attributeConfigs.apply_skybox_image != 0) {
                            viewerHtml += ' skybox-image="'+webArConfig.attributeConfigs.skybox_image+'" ';
                        }
                        
                        if (webArConfig.attributeConfigs.loading != '') {
                            viewerHtml += ' loading="'+webArConfig.attributeConfigs.loading+'" ';
                        }
                        if (webArConfig.attributeConfigs.poster != '') {
                            viewerHtml += ' poster="'+webArConfig.attributeConfigs.poster+'" ';
                        }

                        if (webArConfig.iosModelUrl != "") {
                            viewerHtml += ' ios-src="'+webArConfig.iosModelUrl+'" ';
                        }
                        ///////////
                        viewerHtml += '>';
                        ////Show Custom AR Button////
                        if (webArConfig.showCustomButton) {
                            viewerHtml += '<button slot="ar-button" id="ar-button">';
                            viewerHtml += webArConfig.showCustomButtonText;
                            viewerHtml += '</button>';
                        }
                        //////////////////////////

                        ////Show Associated Products's GLB Models-START////
                        if (associatedProducts.length > 0 && showSeperateGlb) {
                            let thumbNail = "";
                            
                            $.each(associatedProducts, function(assProId, assPro) {
                                thumbNail = assPro["thumbnail"];
                                
                                if (webArConfig.attributeConfigs.poster != '' && thumbNail == "") {
                                    thumbNail = webArConfig.attributeConfigs.poster;
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
        });

        return $.mage.configurable;
    };
});
