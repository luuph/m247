/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery"
], function ($) {
    'use strict';
    $('form').submit(function () {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
    $("form").bind("invalid-form.validate", function () {
    $(this).find(':submit').prop('disabled', false);
    });
    var dataForm = $('#transfer-form-data');
    var isValidation = dataForm.mage('validation', {});
    $('form').submit(function () {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
    $("form").bind("invalid-form.validate", function () {
        $(this).find(':submit').prop('disabled', false);
    });
});