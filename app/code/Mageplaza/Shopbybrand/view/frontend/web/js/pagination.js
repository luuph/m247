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
        $('.pagination-container .item').on('click', 'a', function (event) {
            event.preventDefault();
            var href = $(this).attr('href'),
                metaTag = document.querySelector('meta[name="robots"]');
            $.ajax({
                url: href,
                method: 'GET',
                showLoader: true,
                success: function(response) {
                    $('.brand-mix-container.products-grid').html(response.data);
                },
                error: function(error) {
                    console.log(error);
                }
            });

            if (config.configSeo !== '0' && metaTag) {
                if (href.indexOf('p=') !== -1) {
                    metaTag.setAttribute('content', 'NOINDEX,NOFOLLOW');
                } else {
                    metaTag.setAttribute('content', 'INDEX,FOLLOW');
                }
            }
        });
    };
});
