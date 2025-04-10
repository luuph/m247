/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal"
], function ($, $t, modal) {
    'use strict';

    $.widget('affiliatepro.banner', {

        _create: function () {
            var bannerId        = this.options.bannerId,
                elbannerIdClick = '#' + bannerId,
                elpopup         = '.bnlink-refer-' + bannerId;

            var modalOption = {
                'type': 'popup',
                'title': $t('Link and Script Refer Banner'),
                'modalClass': 'mp-affiliate-banner',
                'responsive': true,
                'innerScroll': true,
                'trigger': elbannerIdClick,
                'buttons': []
            };

            modal(modalOption, elpopup);
        }
    });

    return $.affiliatepro.banner;
});
