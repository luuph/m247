define([
    'Magento_Ui/js/grid/columns/column',
    'mageUtils'
], function (Column, utils) {
    'use strict';

    return Column.extend({
        defaults: {
            link: 'link',
            bodyTmpl: 'Amasty_RmaReports/grid/link'
        },

        getLink: function (record) {
            return utils.nested(record, this.link);
        },

        isLink: function (record) {
            return !!utils.nested(record, this.link);
        }
    });
});
