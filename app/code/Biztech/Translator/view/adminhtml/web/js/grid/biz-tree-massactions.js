define(['jquery','ko'],function ($,ko) {
    'use strict';
    var mixin = {

        applyAction: function (actionIndex) {
            var action = this.getAction(actionIndex),
            visibility;

            if (actionIndex === 'massaction_product_translate' && action.visible) {
                $.ajax({
                    url: action.url,
                    type: 'GET',
                    showLoader: true
                })
                .done(function(response) {
                    if (response.status == 1) {
                        var message = '<div class="messages"><div class="message message-warning warning">'
                        + response.msg
                        + '<div data-ui-id="messages-message-warning"></div></div></div>';

                        if ($('#messages').length == 1) {
                            $('#messages').html(message);
                        } else {
                            $('.page-main-actions').after('<div id="messages">' + message + '</div>');
                        }
                        $(document).scrollTop(0);
                    } else {
                        $('#messages').html('');
                    }
                });
            } else {
                if (action.visible) {
                    visibility = action.visible();

                    this.hideSubmenus(action.parent);
                    action.visible(!visibility);

                    return this;
                }
            }


            return this._super(actionIndex);
        },

        /**
         * Recursive initializes observable actions.
         *
         * @param {Array} actions - Action objects.
         * @param {String} [prefix] - An optional string that will be prepended
         *      to the "type" field of all child actions.
         * @returns {Massactions} Chainable.
         */
        recursiveObserveActions: function (actions, prefix) {
            _.each(actions, function (action) {
                if (prefix) {
                    action.type = prefix + '.' + action.type;
                }

                if (action.actions) {
                    action.visible = ko.observable(false);
                    action.parent = actions;
                    this.recursiveObserveActions(action.actions, action.type);
                }
            }, this);

            return this;
        },

    };

    return function (target) { // target == Result that Magento_Ui/.../columns returns.
        return target.extend(mixin); // new result that all other modules receive
    };
});