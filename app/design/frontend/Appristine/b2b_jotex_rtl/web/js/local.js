define([
    "jquery",
    "mage/accordion"
], function($, accordion){
    "use strict";

    $.widget("mage.acc", accordion, {
        options: {
            activeAll: false
        },

        _callCollapsible: function() {
            if((typeof this.options.active) === "string") {
                this.options.active = this.options.active.split(" ").map(function(item) {
                    return parseInt(item, 10);
                });
            }
            var self = this,
                disabled = false,
                active = false;

            $.each(this.collapsibles, function(i) {
                disabled = active = false;
                if($.inArray(i,self.options.disabled) !== -1) {
                    disabled = true;
                }
                if($.inArray(i,self.options.active) !== -1 || self.options.activeAll) {
                    active = true;
                }
                self._instantiateCollapsible(this,i,active,disabled);
            });
        }
    });

    return $.mage.acc;
});