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

    return function (SwatchRenderer) {
        
        $.widget('mage.SwatchRenderer', SwatchRenderer, {
            /**
             * Update [gallery-placeholder] or [product-image-photo]
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isInProductView
             */
            updateBaseImage: function (images, context, isInProductView) {
                var self = this.options.jsonConfig.webArConfig; //Get WebAR Config.

                ///Check if 3D Model Display is Enabled
                if (!self.displayModel) {
                    return this._super(images, context, isInProductView);
                }
                ///Check if 'Media Gallery Display with 3D Model' is Enabled
                if (!self.canShowOtherImages) {
                    return this._super(images, context, isInProductView);
                }
                //Check if product have 3D model or not
                if (self.modelUrl == "" || self.modelUrl == null) {
                    return this._super(images, context, isInProductView);
                }

                var justAnImage = images[0],
                    initialImages = this.options.mediaGalleryInitial,
                    imagesToUpdate,
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    isInitial;

                if (isInProductView) {
                    if (_.isUndefined(gallery)) {
                        context.find(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                            this.updateBaseImage(images, context, isInProductView);
                        }.bind(this));

                        return;
                    }

                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                    isInitial = _.isEqual(imagesToUpdate, initialImages);

                    if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                        //Remove Extra AR Models
                        initialImages = this._removeExtraARModelfromArray(initialImages);
                        //////////////
                        imagesToUpdate = imagesToUpdate.concat(initialImages);
                    }

                    imagesToUpdate = this._setImageIndex(imagesToUpdate);

                    gallery.updateData(imagesToUpdate);
                    this._addFotoramaVideoEvents(isInitial);
                } else if (justAnImage && justAnImage.img) {
                    context.find('.product-image-photo').attr('src', justAnImage.img);
                }
            },

            /**
             * Callback which fired after gallery gets initialized.
             *
             * @param {HTMLElement} element - DOM element associated with a gallery.
             */
            _onGalleryLoaded: function (element) {
                var self = this.options.jsonConfig.webArConfig; //Get WebAR Config.

                ///Check if 3D Model Display is Enabled
                if (!self.displayModel) {
                    return this._super(element);
                }
                ///Check if 'Media Gallery Display with 3D Model' is Enabled
                if (!self.canShowOtherImages) {
                    return this._super(element);
                }
                //Check if product have 3D model or not
                if (self.modelUrl == "" || self.modelUrl == null) {
                    return this._super(element);
                }

                var galleryObject = element.data('gallery');

                ////////////
                var currImgs = galleryObject.returnCurrentImages();
            
                //Load Model Viewer
                this._load3DModel();

                //Push AR Model in images
                var self = this.options.jsonConfig.webArConfig;
                var showCustomImageInThumbnail = self.showCustomImageInThumbnail;
                var modelThumbnailImg = self.mainImageData;
                
                if (showCustomImageInThumbnail 
                    && self.attributeConfigs.model_thumbnail != '') {
                    modelThumbnailImg = self.attributeConfigs.model_thumbnail;
                }
                this._pushARModel(currImgs, modelThumbnailImg);
                //////////////////

                this.options.mediaGalleryInitial = currImgs;

                ///Update Current Images in Media Gallery///
                galleryObject.updateData(currImgs);
            },

            /**
             * Load media gallery using ajax or json config.
             *
             * @private
             */
            _loadMedia: function () {
                var self = this.options.jsonConfig.webArConfig; //Get WebAR Config.

                ///Check if 3D Model Display is Enabled
                if (!self.displayModel) {
                    return this._super();
                }
                ///Check if 'Media Gallery Display with 3D Model' is Enabled
                if (!self.canShowOtherImages) {
                    return this._super();
                }
                //Check if product have 3D model or not
                if (self.modelUrl == "" || self.modelUrl == null) {
                    return this._super();
                }

                var $main = this.inProductList ?
                        this.element.parents('.product-item-info') :
                        this.element.parents('.column.main'),
                    images;

                if (this.options.useAjax) {
                    this._debouncedLoadProductMedia();
                }  else {
                    images = this.options.jsonConfig.images[this.getProduct()];

                    if (!images) {
                        images = this.options.mediaGalleryInitial;
                    }

                    ////////////Load Model Viewer/////
                    this._load3DModel();
                    //Remove Extra AR Models
                    images = this._removeExtraARModelfromArray(images);

                    var self = this.options.jsonConfig.webArConfig;
                    var showCustomImageInThumbnail = self.showCustomImageInThumbnail;
                    var modelThumbnailImg = self.mainImageData;
                    
                    if (showCustomImageInThumbnail 
                        && self.attributeConfigs.model_thumbnail != '') {
                        modelThumbnailImg = self.attributeConfigs.model_thumbnail;
                    }

                    //Push AR Model in images
                    this._pushARModel(images, modelThumbnailImg);
                    //////////////////
            
                    this.updateBaseImage(this._sortImages(images), $main, !this.inProductList);
                }
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
                var self = this.options.jsonConfig.webArConfig;

                var associatedProductsThumbs = self.associatedProductThumbs;

                //Check if simpleProduct OR this.getProduct() is valid and showCustomImageInThumbnail is false
                if (typeof this.getProduct() != "undefined" && !self.showCustomImageInThumbnail) {
                    modelThumbnailImg = associatedProductsThumbs[this.getProduct()];
                }

                if (typeof imagesArray != "undefined" && 
                (self.modelUrl != "" || self.modelUrl != null)) {
                    imagesArray.unshift({
                        thumb: modelThumbnailImg,
                        'src': self.modelUrl,
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
                var self = this.options.jsonConfig.webArConfig;
                var optionTextVal = "";
                var optionTextArr = []; 
                var selectedText = "";
                var selectedVal = "";
                var selectedSwatchAttrId = 0;
                var defaultVariantAttribute = self.defaultVariantAttribute;
            
                if ($('.product-options-wrapper select[id^="attribute"]').find().length) {
                    selectedText = $('.product-options-wrapper select[id^="attribute"] option:selected').text();
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
              
                //If showSeperateGlb is true then set AR Model based on selected variant//
                if (self.showSeperateGlb) {
                    var associatedProducts = self.associatedProducts;
                    var selectedProduct = $("input[name='selected_configurable_option']").val();
                    if (!selectedVal && selectedProduct) {
                        selectedProduct = self.firstAssociatedProduct;
                    }
                  
                    if (typeof this.getProduct() != "undefined") {
                        selectedProduct = this.getProduct();
                    }

                    if (typeof selectedProduct == "undefined" || selectedProduct == "") {
                        selectedProduct = self.firstAssociatedProduct;
                    }
                    
                    if (typeof associatedProducts[selectedProduct] != "undefined") {
                        self.modelUrl = associatedProducts[selectedProduct]["modelUrl"];
                        self.iosModelUrl = associatedProducts[selectedProduct]["iosModelUrl"];
                    }
                }
                ////
                
                return optionTextVal;
            },


            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var self = this.options.jsonConfig.webArConfig;
                var modelViewer = $("model-viewer");
                var mainVariantAttributeId = self.defaultVariantAttribute;

                var swatchId = $this.attr("id");
                var idParts = swatchId.split('-');
                var selectedSwatchAttrId = 0;

                for (let index=0; index<idParts.length; index++) {
                    if ($.isNumeric(idParts[index])) {
                        selectedSwatchAttrId = idParts[index];
                        break;
                    }
                }
                
                if (typeof modelViewer == "object" 
                    && selectedSwatchAttrId == parseInt(mainVariantAttributeId)) {
                    modelViewer.variantName = $this.attr("data-option-label");
                    modelViewer.attr("variant-name", $this.attr("data-option-label"));
                }

                this._super($this, $widget);
            },

            /**
             * Load 3D Model Viewer
             * 
             * @private
             */
            _load3DModel: function() {
                var thisJs = this;
                var self = this.options.jsonConfig.webArConfig;
                var divFotorama = $('div.gallery-placeholder > div.fotorama');

                var associatedProducts = self.associatedProducts;
                var showSeperateGlb = self.showSeperateGlb;

                var optionText = "";
                //Get Selected Variant Value
                optionText = this._getSelectedVariantValue();

                if (self.modelUrl == "" && showSeperateGlb) {
                    return;
                }
                
                divFotorama.on('fotorama:load', function fotorama_onLoad(e, fotorama, extra) {
                    optionText = thisJs._getSelectedVariantValue();
                    
                    if (extra.frame.type === 'ARModel' && extra.frame.src != "") {
                        var viewerHtml = '';
                        viewerHtml += '<model-viewer id="wk3dimage" ';
                        viewerHtml += 'data-js-focus-visible ';
                        viewerHtml += 'src="'+self.modelUrl+'" ';
                        viewerHtml += 'alt="A 3D model of an product" camera-controls  ';
                        viewerHtml += ' camera-orbit="33deg 67deg auto" ';

                        if (self.productHasOptions) {
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
                        if (self.attributeConfigs.auto_rotate == 1) {
                            viewerHtml += ' auto-rotate ';
                            if (self.attributeConfigs.auto_rotate_delay != '') {
                                viewerHtml += ' auto-rotate-delay="'+self.attributeConfigs.auto_rotate_delay+'" ';
                            }
                        }
                        if (self.attributeConfigs.disable_zoom == 1) {
                            viewerHtml += ' disable-zoom ';
                        }
                        if (self.attributeConfigs.disable_tap == 1) {
                            viewerHtml += ' disable-tap ';
                        }
                        if (self.attributeConfigs.touch_action != '') {
                            viewerHtml += ' touch-action="'+self.attributeConfigs.touch_action+'" ';
                        }
                        if (self.attributeConfigs.interpolation_decay != '') {
                            viewerHtml += ' interpolation-decay="'+self.attributeConfigs.interpolation_decay+'" ';
                        }

                        if (self.attributeConfigs.shadow_intensity != '-1') {
                            viewerHtml += ' shadow-intensity="'+self.attributeConfigs.shadow_intensity+'" ';
                        }
                        if (self.attributeConfigs.exposure != '-1') {
                            viewerHtml += ' exposure="'+self.attributeConfigs.exposure+'" ';
                        }
                        if (self.attributeConfigs.shadow_softness != '-1') {
                            viewerHtml += ' shadow-softness="'+self.attributeConfigs.shadow_softness+'" ';
                        }
                        if (self.attributeConfigs.apply_environment_image == 1) {
                            if (self.attributeConfigs.environment_image != '' && self.attributeConfigs.environment_image != 'envurl') {
                                viewerHtml += ' environment-image="'+self.attributeConfigs.environment_image+'" ';
                            } else if(self.attributeConfigs.environment_image == 'envurl' 
                                && self.attributeConfigs.environment_image_url != '') {
                                    viewerHtml += ' environment-image="'+self.attributeConfigs.environment_image_url+'" ';
                            }
                        }
                       
                        if (self.attributeConfigs.skybox_image != ''
                            && self.attributeConfigs.apply_skybox_image != 0) {
                            viewerHtml += ' skybox-image="'+self.attributeConfigs.skybox_image+'" ';
                        }
                        
                        if (self.attributeConfigs.loading != '') {
                            viewerHtml += ' loading="'+self.attributeConfigs.loading+'" ';
                        }
                        if (self.attributeConfigs.poster != '') {
                            viewerHtml += ' poster="'+self.attributeConfigs.poster+'" ';
                        }

                        if (self.iosModelUrl != "") {
                            viewerHtml += ' ios-src="'+self.iosModelUrl+'" ';
                        }
                        ///////////
                        viewerHtml += '>';
                        ////Show Custom AR Button////
                        if (self.showCustomButton) {
                            viewerHtml += '<button slot="ar-button" id="ar-button">';
                            viewerHtml += self.showCustomButtonText;
                            viewerHtml += '</button>';
                        }
                        //////////////////////////

                        ////Show Associated Products's GLB Models-START////
                        if (associatedProducts.length > 0 && showSeperateGlb) {
                            let thumbNail = "";
                            
                            $.each(associatedProducts, function(assProId, assPro) {
                                thumbNail = assPro["thumbnail"];
                                
                                if (self.attributeConfigs.poster != '' && thumbNail == "") {
                                    thumbNail = self.attributeConfigs.poster;
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

        return $.mage.SwatchRenderer;
    };
});
