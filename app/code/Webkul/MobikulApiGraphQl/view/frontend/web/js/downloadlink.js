/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/template"
], function ($, mageTemplate) {
    "use strict";
    $.widget("downloadlink.downloadlinkQuery", {
        options: {},
        _create: function () {
            var self = this;
            window.rememberClicked = function () {
                $.ajax({
                    url : self.options.rememberUrl,
                    type: "POST",
                    dataType: "json",
                    data: {}
                });
                $(".mk-downloadlink-container").slideUp(500);
            }
        }
    });
    return $.downloadlink.downloadlinkQuery;
});