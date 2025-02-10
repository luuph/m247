define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Meetanshi_ImageClean/templates/grid/cells/custom/popup.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, imagePreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        gethtml: function (row) {
            return row[this.index + '_img'];
        },
        getImages: function (row) {
            return row[this.index+'_img'];
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                imagePreviewTemplate,
                {
                    html: this.gethtml(row),
                    label: '',
                    customerid:'01',
                    img: this.getImages(row),
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: '',
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});