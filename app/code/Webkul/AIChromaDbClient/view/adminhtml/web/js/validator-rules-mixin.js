/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIChromaDbClient
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery'
], function ($) {
    'use strict';
    return function (target) {
        $.validator.addMethod(
            'validate-server-protocol',
            function (value) {
                if (value.includes('http://') || value.includes('https://')) {
                    return true;
                } else {
                    return false;
                }
            },
            $.mage.__('Please enter server name with protocol')
        );
        return target;
    };
});
