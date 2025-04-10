define([
    'jquery'
], function ($) {
    'use strict';

    return function (target) {
        return $.extend(target, {
            setStoreId: function (id) {
                this.storeId = id;
                this.storeSelectorHide();
                this.sidebarShow();
                //this.loadArea(['header', 'sidebar','data'], true);
                this.dataShow();
                this.loadArea(['header', 'data'], true);
                // location.reload(); // Commented out
            }
        });
    };
});
