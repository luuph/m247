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
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    "use strict";
    return function(config) {
        $(document).ready(function() {
            var templateType = config.templateType;
            $("#preview_" + templateType).click(function () {
                $("#mp-form-key-" + templateType).val(window.FORM_KEY);
                var templateId = $("#default_template_" + templateType).val();
                $("#template-id-" + templateType).val(templateId);
                $("#mp-submit-" + templateType).trigger('click');
                $("#iframe_" + templateType).contents().find('html').html('<p>' + $t('Loading....') + '</p>');
                $("#iframe_" + templateType).show();
            });
        });
    };
});
