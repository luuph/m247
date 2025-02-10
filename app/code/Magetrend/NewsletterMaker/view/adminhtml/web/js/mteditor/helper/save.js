/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var saveHelper = (function($){
    var config = {

        template_id: 0,

        action: {
            saveUrl: ''
        },
        formKey: ''
    };

    var editorFrame;

    var canSave = true;

    var autoSave = false;

    var init = function(options) {
        $.extend(config, options);
        editorFrame = $('iframe#preview-frame');
        initEvent();
    };

    var initEvent = function() {
        $('button[data-action="save"]').unbind('click').click(function(){
            if (canSave) {
                save();
            }
        });
    };

    var save = function(callBack) {
        $('*[data-action="save"]').text('Saving...');
        sendRequest(
            config.action.saveUrl,
            {
                template_id: config.template_id,
                head: JSON.stringify(sourceEditor.getHead()),
                body: JSON.stringify(sourceEditor.getBody())
            },

            function(response) {
                if (callBack) {
                    callBack(response);
                }

                $('*[data-action="save"]').text('Save');
                mtEditor.removedBlockList = {};
            }
        )
    };

    var sendRequest = function(url, data, callBack) {
        data.form_key = config.formKey;
        $.ajax({
            url: url+'?isAjax=1',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                callBack(response);
            }
        });
    };

    return {
        init: init,
        save: save
    };
})(jQuery);