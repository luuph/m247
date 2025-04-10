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
define(['jquery'], function($) {
    return function(config) {
        $('.show-more').on('click', 'a', function (event) {
            event.preventDefault();
            var char = $(this).attr('value'),
                countBrandChar = $('.brand-list-container' + '.' + char).find('li').length,
                url = config.loadUrl + '?char=' + char + '&count=' + countBrandChar;

            $.ajax({
                url: url,
                method: 'GET',
                showLoader: true,
                success: function(response) {
                    $('.' + char +' .brand-list-content .product-items').html(response.data);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $('.product-list-container .action').on('click' ,function () {
            $(this).toggleClass('active');
            $(this).parents('.product-list-container').find('.brand-list-content').toggle();
        });
    };
});
