define([
    'jquery',
    'Magento_Customer/js/customer-data',
	'Magezon_Core/js/jquery.magnific-popup.min'
], function ($, customerData) {
    'use strict';

    $.widget('lb.profile', {
        options: {
            popupSelector: '#lookbook-popup'
        },

    	_create: function () {
            var imgElem         = $('.lookbook-profile-image img');
            var imgClientHeight = imgElem[0].clientHeight;
            this.renderMarkers();

            if (this.options.layoutType == 'type2') {
                this.productHover();
                this.markerHover();
                this.addAllToCartButton();
                this.quickView();
            	$('.lookbook-profile-products').css('height', imgClientHeight - 10);
            }
                this.markerClick();
            
        },

        showOverlay: function() {
            $('.overlay', this.element).addClass('active');
        },

        hideOverlay: function() {
            $('.overlay', this.element).removeClass('active');
        },

        productHover: function() {
            var self = this;
            $('.lookbook-product', self.element).hover(function() {
                self.showOverlay();
                $('.lookbook-profile-marker' + $(this).data("id")).addClass('active lookbook-animation');
            }, function(){
                self.hideOverlay();
                $('.lookbook-profile-marker' + $(this).data("id")).removeClass('active lookbook-animation');
            });
        },

        markerHover: function() {
            var self = this;
            $('.lookbook-profile-marker', self.element).hover(function() {
                let elem = $(this);
                let id   = elem.data("id");
                let scrollTop  = $('.lookbook-profile-products', self.element).scrollTop();
                let parentOffset  = $('.lookbook-profile-products', self.element).offset();
                let productOffset = $('.lookbook-product' + id, self.element).offset();

                if (productOffset) {
                    let scrollTop1 = productOffset.top - parentOffset.top;

                    elem.addClass('active');
                    self.showOverlay();
                    $('.lookbook-product' + id, self.element).addClass('active');

                    if (scrollTop > 0) {
                        scrollTop1 = scrollTop - parentOffset.top + productOffset.top;
                    }

                    $('.lookbook-profile-products', self.element).animate({scrollTop: scrollTop1}, 1000);
                }
                
            }, function() {
                let elem = $(this);
                let id   = elem.data("id");
                elem.removeClass('active')
                self.hideOverlay();
                $('.lookbook-product' + id, self.element).removeClass('active');
            });
        },

        markerClick: function() {
            var self = this;
            $('.lookbook-profile-marker span.lookbook-btn-popup', self.element).click(function(e) {
                if ($(this).data("id")) {
                    if ($(this).parent().hasClass('lookbook-product-show')) {
                        $(this).parent().removeClass('lookbook-product-show');
                        self.hideOverlay();
                    } else {
                        $('.lookbook-profile-marker').removeClass('lookbook-product-show');
                        $(this).parent().addClass('lookbook-product-show');
                        self.showOverlay();
                    }

                    $(document).click(e => {
                        if (!$('.lookbook-profile-marker').is(e.target) 
                        && $('.lookbook-profile-marker').has(e.target).length === 0)
                        {
                            self.hideOverlay();
                            $('.lookbook-profile-marker', self.element).removeClass('lookbook-product-show');
                        }
                    });
                }
            });
        },
 
        addAllToCartButton: function () {
            var self = this;
            var addToCartUrl = self.options.addToCartUrl;
            $('.lookbook-ajax-all-prt', self.element).click(function() {
                var items = [];
                var ids   = [];
                
                $('.lookbook-product').each(function() {
                    let elem = $(this);
                    let productId   = elem.data('id');
                    let productType = elem.data('type');
                    let productUrl  = elem.data('url');
                    let options = '';

                    if (productType !== 'simple') {
                        options = $('.lookbook-product' + productId + ' textarea.options').val();
                        if (!options) {
                            ids.push(productId);
                        }
                    }
                    
                    let row = {
                        product: productId,
                        options: options,
                        qty: 1,
                        quote_type: ''
                    }
                    items.push(row);
                });

                for ( var i = 0, l = ids.length; i < l; i++ ) {
                    let options = $('.lookbook-product' + ids[i] + ' textarea.options').val();
                    if (!options) {
                        $('.lookbook-product' + ids[i] + ' .lookbook-btn-quick-view').triggerHandler('click').pause();
                    }
                }

                if (items.length) {
                    $('.lookbook-loading').css('display', 'block');
                    $.ajax({
                        type: "POST",
                        url: addToCartUrl,
                        data: {items: items, mode: "lookbook"},
                        dataType: 'json',
                        success: function (res) {
                            $('.lookbook-loading').css('display', 'none');
                            if (res.message) {
                                alert(res.message);
                            }
                            if (res.redirectUrl) {
                                window.location.href = res.redirectUrl;
                            }
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        }
                    });
                } else {
                    this.submitLoading(false);
                }
            });
        },

        quickView: function() {
            var self  = this;
            var popupSelector = self.options.popupSelector;
            
            $('.lookbook-btn-quick-view', self.element).click(function() {
                var elem = $(this);
                var id   = elem.data("id");
                var url  = elem.data("url") + '?options=cart';
                var options = $('.lookbook-product' + id + ' textarea.options', self.element).val();
                $('.lookbook-popup-loader', self.element).css('display', 'block');

                $.ajax({
                    url: url,
                    data: { 
                        product: id, 
                        mgz_lb: 1, 
                        mode: 'lookbook'
                    },
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function () {
                        $(popupSelector + ' .lookbook-popup-content').html('');
                        $(popupSelector).addClass('lookbook-popup-loading');
                    },
                    success: function (res) { 
                        $('.lookbook-popup-loader', self.element).css('display', 'none');
                        if (res.message) alert(res.message);
                        if (res.html) {
                            $(popupSelector + ' .lookbook-popup-content').html(res.html);
                            $(popupSelector + ' .lookbook-popup-content').trigger('contentUpdated');
                            $(popupSelector + ' #product_addtocart_form').validation();
                            $(popupSelector).removeClass('lookbook-popup-loading');

                            setTimeout(function() {
                                self.editOptions(options);
                            }, 200);
                        }
                    }
                });
            });
            
            self.magnificPopup();
        },

        parseParams: function(str) {
            var re = /([^&=]+)=?([^&]*)/g;
            var decodeRE = /\+/g;
            var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};
            var params = {}, e;
            while ( e = re.exec(str) ) { 
                var k = decode( e[1] ), v = decode( e[2] );
                if (k.substring(k.length - 2) === '[]') {
                    k = k.substring(0, k.length - 2);
                    (params[k] || (params[k] = [])).push(v);
                }
                else params[k] = v;
            }
            return params;
        },

        editOptions: function(options) {
            var newOptions = this.parseParams(options);
            var popupSelector = this.options.popupSelector;
            $(popupSelector).find('input, select, textarea').each(function(index, el) {
                var name = $(this).attr('name');
                if (newOptions[name]) {
                    $(this).val(newOptions[name]);
                    $(this).trigger('change');
                    if ($(this).hasClass('swatch-input')) {
                        var parent = $(this).parents('.swatch-attribute');
                        parent.find('.swatch-option[data-option-id=' + newOptions[name] + ']').eq(0).trigger('click');
                    }
                }
            });
        },

        magnificPopup: function() {
            var popupSelector = this.options.popupSelector;
            $('.lookbook-btn-quick-view', this.element).magnificPopup({
                items: {
                    src: popupSelector
                },
                type: 'inline',
                removalDelay: 300,
                mainClass: 'lookbook-popup',
                fixedContentPos: true,
                fixedBgPos: true,
                overflowY: 'auto',
                showCloseBtn: false,
                callbacks: {
                    beforeOpen: function() {
                        $('body').addClass('lookbook-popup');
                        this.st.mainClass = $(popupSelector).attr('data-effect');
                    }, 
                    beforeClose: function() {
                        this.st.mainClass = '';
                        $(popupSelector).css('transition', '');
                        $('body').removeClass('lookbook-popup');
                    }
                },
            }, 0);
        },

        renderMarkers: function () {
            var self            = this;
            var imgElem         = $('.lookbook-profile-image img', self.element);
            var imgWidth        = imgElem[0].naturalWidth;
            var imgHeight       = imgElem[0].naturalHeight;
            var imgClientWidth  = imgElem[0].clientWidth;
            var markerWidth     = self.options.markerWidth;

            $('.lookbook-profile-marker', self.element).each(function() {
                let left       = $(this).data("left");
                let top        = $(this).data("top");
                let lefPercent = (left / imgWidth) * 100;
                let topPercent = (top / imgHeight) * 100;
                let markerClientWidth = (imgClientWidth * markerWidth) / imgWidth;
                $(this).css({
                    "left": lefPercent + "%", 
                    "top": topPercent + "%"
                });
                $('.lookbook-profile-marker span.lookbook-marker', self.element).css({
                    "width": markerClientWidth, 
                    "height": markerClientWidth, 
                    "line-height": markerClientWidth + "px", 
                    "background-image": 'url("' + self.options.markerImage + '")'
                });
                if (markerClientWidth > 20) {
                    $('.lookbook-profile-marker i').css({ "font-size" : '10px' });
                } else {
                    $('.lookbook-profile-marker i').css({ "font-size" : '8px' });
                }
                $('.lookbook-profile-product.lookbook-profile-product-popup-top', self.element).css({
                    "bottom": markerClientWidth + 15
                });
                $('.lookbook-profile-product.lookbook-profile-product-popup-right', self.element).css({
                    "left": markerClientWidth + 15
                });
                $('.lookbook-profile-product.lookbook-profile-product-popup-bottom', self.element).css({
                    "top": markerClientWidth + 15
                });
                $('.lookbook-profile-product.lookbook-profile-product-popup-left', self.element).css({
                    "right": markerClientWidth + 15
                });
            });
        }
    });

    return $.lb.profile;
});