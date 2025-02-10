/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var popup = (function() {

    var beforeResize = null;

    var isOpen = false;

    var bgLayer = null;

    var popupBox = null;

    var lastTopPosition = 0;

    var config = {};

    var init = function(settings) {
        config = {
            closeSelector:          '#esns_box_close',
            backgroundSelector:     '#esns_background_layer',
            boxSelector:            '#esns_box_layer',
            layerClose:             true,
            autoPosition:           true,
            disableClose:           false,
            size:                   'normal'
        };
        $.extend(config, settings );

        setup();
    };

    var setup = function() {

        bgLayer = $(config.backgroundSelector);
        popupBox = $(config.boxSelector);

        if (config.autoPosition) {
            $(document).scroll(function() {
                eventScroll();
            });

            $(window).resize(function() {
                eventResize();
            });
        }

        $(config.closeSelector).click(function(){
            close();
        });

        if (config.layerClose) {
            $(config.backgroundSelector).click(function(e) {
                if ('#'+e.target.id == config.backgroundSelector) {
                    close();
                }
            });
        }
    };

    var content = function(options, beforeOpen, callback, beforeClose) {
        config.disableClose = options.disableClose;
        if (config.disableClose) {
            $(config.closeSelector).hide();
        } else {
            $(config.closeSelector).show();
        }


        $('#esns_box_content').html('').append($('#content_popup').clone().html());
        $('#esns_box_content .popup-content').html($(options.contentSelector).html());
        beforeOpen();
        popup.open();
        $('#esns_box_content *[data-action="0"]').click(function(){
            beforeClose();
            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }

        });
        $('#esns_box_content a[data-action="1"]').click(function(e){
            callback(e);
            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }
        });
    };

    var initPopup = function(options) {

        config.disableClose = options.disableClose;

        if (options.beforeResize) {
            beforeResize = options.beforeResize;
        }

        if (config.disableClose) {
            $(config.closeSelector).hide();
        } else {
            $(config.closeSelector).show();
        }


        $('#esns_box_content').html('').append($('#content_popup').clone().html());
        $('#esns_box_content .popup-content').html($(options.contentSelector).html());
        if (options.beforeOpen) {
            options.beforeOpen();
        }

        eventResize();
        popup.open();
        if (options.afterOpen) {
            options.afterOpen();
        }


        $('#esns_box_content *[data-action="0"]').click(function(){
            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }
            if (options.cancel) {
                options.cancel();
            }
        });

        $('#esns_box_content a[data-action="1"]').click(function(e){
            if (options.success) {
                options.success(e);
            }

            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }
        });
    };

    var confirm = function(options, callbackYes, callbackNo) {
        $('#esns_box_content').html('').append($('#confirm_popup').clone().html());
        $('#esns_box_content .popup-msg').html(options.msg);
        popup.open();
        $('#esns_box_content a[data-action="0"]').click(function(){
            callbackNo();
            if (!options.disableAutoClose) {
                popup.close();
            }

        });
        $('#esns_box_content a[data-action="1"]').click(function(){
            callbackYes();
            if (!options.disableAutoClose) {
                popup.close();
            }
        });
    };

    var open = function() {
        if(!isOpen) {
            bgLayer.fadeIn();
            bgLayer.css('height', $(document).height()+'px');
            popupBox.css('margin-top', getTopPosition()+'px');
            isOpen = true;
        }
    };

    var close = function(forceClose) {
        if (!forceClose && config.disableClose == true) {
            return;
        }
        if (isOpen) {
            bgLayer.fadeOut();
            isOpen = false;
        }
    };

    var getTopPosition = function() {
        var scrollTop = jQuery(document).scrollTop();
        var windowH = jQuery(window).height();
        var boxH = popupBox.height();
        var boxTop = 0;
        if (windowH <= boxH) {
            boxTop = scrollTop;
        } else {
            boxTop = scrollTop + ((windowH - boxH ) /2);
        }
        return boxTop;
    };

    var eventScroll = function()
    {
        var windowH = $(window).height();
        var boxH = popupBox.height();
        var scrollTop = $(document).scrollTop();
        var diff = Math.abs(lastTopPosition - scrollTop);
        if (windowH <= boxH) {
            return;
        }

        if (diff > 150
            || scrollTop == 0
            || scrollTop + $(window).height() == $(document).height()
        ) {
            lastTopPosition = scrollTop;
            popupBox.css('margin-top', getTopPosition()+'px');
        }
    };

    var eventResize = function() {
        if (beforeResize != null) {
            beforeResize();
        }

        var winH = $(window).height();
        var winW = $(window).width();
        var boxH = popupBox.height();
        if (winH <= boxH) {
            return;
        }

        popupBox.css('margin-top', getTopPosition()+'px');
        $(config.backgroundSelector).css({
            width: winW+'px',
            height: winH+'px',
        });
    };

    return {
        init:           init,
        config:         config,
        confirm:        confirm,
        content:        content,
        open:           open,
        close:          close,
        eventResize:    eventResize,
        initPopup:      initPopup
    };
})(jQuery);