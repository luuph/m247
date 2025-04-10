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
        let items = config.items || 3;
        var nextPrevButton = config.nextPrevButton > 0 ? config.nextPrevButton : false,
            dotsNav = config.showDotsNav > 0 ? config.showDotsNav : false,
            autoPlay = config.autoPlay > 0 ? config.autoPlay : true,
            autoTimeout = config.autoTimeout ? config.autoTimeout : 4000;


        $(element).owlCarousel({
            margin: 10,
            loop: true,
            nav: nextPrevButton,
            autoHeight: true,
            autoplay: autoPlay,
            autoplayTimeout: autoTimeout,
            autoplayHoverPause: true,
            lazyLoad: true,
            dots: dotsNav,
            responsiveClass: true,
            responsiveBaseElement: '#' + $(element).attr('id'),
            responsive: {
                0: {items: 1},
                360: {items: 2},
                540: {items: items},
                720: {items: items},
                1024: {items: items}
            }
        });
    };
});
