/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    "map": {
        "*": {
            '@webxr/mv/model-viewer-umd' : 'Webkul_WebAr/js/webxr/model-viewer.min',
         }
    },
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable' : {'Webkul_WebAr/js/configurable':true},
            'Magento_Swatches/js/swatch-renderer' : {'Webkul_WebAr/js/swatch-renderer':true}
        }
    }
};