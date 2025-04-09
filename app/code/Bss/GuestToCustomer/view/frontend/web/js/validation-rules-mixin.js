define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Validate that string has specific special characters
     * @param {String} value
     * @return {Boolean}
     */
    function validateSpecialCharacters(value) {
        return /^[\p{L}\p{M},\-_.’'`&\s\d]{1,255}$/u.test(value);
    }

    return function (validator) {
        validator.addRule(
            'validate-custom-name',
            function (value) {
                if ($.mage.isEmptyNoTrim(value)) {
                    return true;
                }

                return validateSpecialCharacters(value);
            },
            $.mage.__('The name contains invalid characters. Only letters, numbers, and , - _ . ’ \' ` & are allowed.')
        );

        return validator;
    };
});
