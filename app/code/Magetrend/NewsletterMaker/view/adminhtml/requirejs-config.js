/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var config = {
    map: {
        '*': {
            mtEditor_jquery: 'Magetrend_NewsletterMaker/js/mteditor/jquery-2.1.3',
            mtEditor_bootstrap: 'Magetrend_NewsletterMaker/js/mteditor/bootstrap.min',
            mtEditor_cookie: 'Magetrend_NewsletterMaker/js/mteditor/jquery.cookie',
            mtEditor_jquery_ui: 'Magetrend_NewsletterMaker/js/mteditor/jquery-ui',
            mtEditor_ui_widget: 'Magetrend_NewsletterMaker/js/mteditor/jquery.ui.widget',
            mtEditor_iframe_transport: 'Magetrend_NewsletterMaker/js/mteditor/jquery.iframe-transport',
            mtEditor_file_upload: 'Magetrend_NewsletterMaker/js/mteditor/jquery.fileupload',
            mtEditor_helper: 'Magetrend_NewsletterMaker/js/mteditor/text_edit_helper',
            mtEditor_color_picker: 'Magetrend_NewsletterMaker/js/mteditor/colorpicker',
            mtEditor_popup: 'Magetrend_NewsletterMaker/js/mteditor/popup',
            mtEditor_save_helper: 'Magetrend_NewsletterMaker/js/mteditor/helper/save',
            mtEditor_metis_menu: 'Magetrend_NewsletterMaker/js/mteditor/jquery.metisMenu',
            mtEditor_editor: 'Magetrend_NewsletterMaker/js/mteditor/editor'
        },
        shim: {
            'mtEditor_bootstrap': {
                deps: ['jquery']
            },
            'mtEditor_cookie': {
                deps: ['jquery']
            },
            'mtEditor_jquery_ui': {
                deps: ['jquery']
            },
            'mtEditor_ui_widget': {
                deps: ['jquery']
            },
            'mtEditor_iframe_transport': {
                deps: ['jquery']
            },
            'mtEditor_file_upload': {
                deps: ['jquery']
            },
            'mtEditor_helper': {
                deps: ['jquery']
            },
            'mtEditor_color_picker': {
                deps: ['jquery']
            },
            'mtEditor_popup': {
                deps: ['jquery']
            },
            'mtEditor_save_helper': {
                deps: ['jquery']
            },
            'mtEditor_metis_menu': {
                deps: ['jquery']
            },
            'mtEditor_editor': {
                deps: ['jquery']
            }
        }
    }
};