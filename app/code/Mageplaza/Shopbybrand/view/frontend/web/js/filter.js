/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define(['jquery',
    'Mageplaza_Shopbybrand/js/quick-view',
    'Mageplaza_Shopbybrand/js/modal-popup'
], function($, quickView, modalPopup) {
    'use strict';
    return function(config) {
        var isLoading= false;
        $(document).ready(function() {
            function loadMore(url, element, char) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    showLoader: true,
                    success: function(response) {
                        if (element === ''){
                            if (config.brandListStyle === '0') {
                                $('.brand-mix-container.products-grid').html(response.data);

                            } else {
                                $('.products-grid').html(response.data);
                            }
                        } else {
                            if (config.brandListStyle === '0') {
                                $('.items.product-items li').last().after($(response.data).find('ol.products.list.items.product-items').html().trim());
                                $('ul.items.pages-items').html($(response.data).find("ul.items.pages-items").html().trim());
                                isLoading = false;
                                quickView();
                                modalPopup();
                            }
                        }
                        if (char !== 'all') {
                            $('.pagination-container.toolbar').hide();
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                })
            }

            $('.brand-options .control').on('click', 'a', function (event) {
                event.preventDefault();
                var char = $(this).attr('data-filter');

                if (char !== 'all') {
                    char = $(this).attr('data-filter').substring(1);
                }
                var url = config.loadUrl + '?char=' + char;
                loadMore(url, '', char);
                if (char !== 'all') {
                    char = $(this).attr('data-filter').substring(1);
                }
                enableScroll(char);

            });
            function enableScroll(char) {
                if (char !== 'all') {
                    $(window).bind('scroll', function () {
                        scrollHandler();
                    });
                }
            }

            function scrollHandler() {
                var nextItem = $('li.item.pages-item-next a.action.next'),
                    char = $('.brand-options .control.active .filter').attr('data-filter');
                if (nextItem.length && char !== 'all'){
                    var listEl = $('ol.products.list.items.product-items');
                    if (listEl.length && ($(window).scrollTop() >= listEl.offset().top + listEl.outerHeight() - window.innerHeight)) {
                        if (isLoading === false){
                            loadMore(nextItem.attr('href'), listEl, char);
                            isLoading =true;
                        }
                    }
                }
            }
        });
    };
});
