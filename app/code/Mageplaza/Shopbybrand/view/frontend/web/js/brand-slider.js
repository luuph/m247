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

define([
    'jquery',
    'mageplaza/core/owl.carousel'
], function ($) {
    'use strict';

    return function (config, element) {
        var items = config.items || 3;
        var dots = config?.dots || false;
        var colorNav = config?.color || '#000000';
        $(element).owlCarousel({
            loop: true,
            nav: true,
            margin: 10,
            autoHeight: true,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplayHoverPause: true,
            lazyLoad: true,
            dots: dots,
            responsiveClass: true,
            responsiveBaseElement: '#' + $(element).attr('id'),
            responsive: {
                0: { items: 1 },
                360: { items: 2 },
                540: { items: items },
                720: { items: items },
                1024: { items: items }
            },
            navText: [`<svg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
            "<circle cx=\"20\" cy=\"20\" r=\"20\" fill=\"${colorNav}\"/>\n" +
            "<rect width=\"21.8182\" height=\"21.8182\" transform=\"translate(9.09082 9.09082)\" fill=\"${colorNav}\"/>\n" +
            "<path d=\"M19.1106 19.6557L18.757 20.0092L19.1106 20.3628L22.6379 23.8901C22.7971 24.0493 22.7971 24.3055 22.6379 24.4648C22.4786 24.624 22.2224 24.624 22.0632 24.4648L17.8904 20.292C17.7311 20.1328 17.7311 19.8766 17.8904 19.7173L22.0632 15.5446C22.2217 15.3861 22.4761 15.3853 22.6356 15.5423C22.7952 15.7087 22.7907 15.9755 22.6379 16.1284L19.1106 19.6557Z\" fill=\"black\" stroke=\"white\"/>\n" +
            "</svg>\n`,
                `<svg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
                "<circle cx=\"20\" cy=\"20\" r=\"20\" transform=\"rotate(-180 20 20)\" fill=\"${colorNav}\"/>\n" +
                "<rect width=\"21.8182\" height=\"21.8182\" transform=\"matrix(-1 0 0 -1 30.9092 30.9092)\" fill=\"${colorNav}\"/>\n" +
                "<path d=\"M20.8894 20.3443L21.243 19.9908L20.8894 19.6372L17.3621 16.1099C17.2029 15.9507 17.2029 15.6945 17.3621 15.5352C17.5214 15.376 17.7776 15.376 17.9368 15.5352L22.1096 19.708C22.2689 19.8672 22.2689 20.1234 22.1096 20.2827L17.9368 24.4554C17.7783 24.6139 17.5239 24.6147 17.3644 24.4577C17.2048 24.2913 17.2093 24.0245 17.3621 23.8716L20.8894 20.3443Z\" fill=\"black\" stroke=\"white\"/>\n" +
                "</svg>\n`]
        });
    };
});
