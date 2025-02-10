/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var templateHelper = (function($){

    var getHead = function (documentHtml) {
        var tmp = documentHtml.split('</head>');
        tmp = tmp[0].split('<head');
        tmp = tmp[1].split('>');
        tmp.splice(0, 1);
        return tmp.join('>');
    }

    var getCss = function (documentHtml, includeContainter) {
        var headElement = $('<div>'+getHead(documentHtml)+'</div>');
        var css = '';
        var cssElement;
        if (headElement.find('style[data-css]').length > 0) {
            cssElement = headElement.find('style[data-css]');
        } else {
            cssElement = headElement.find('style');
        }

        if (includeContainter) {
            return $('<div></div>').append(cssElement).html();
        }

        return cssElement.html();
    }

    var getBody = function (documentHtml) {
        var tmp = documentHtml.split('</body>');
        tmp = tmp[0].split('<body');
        tmp = tmp[1].split('>');
        tmp.splice(0, 1);
        return tmp.join('>');
    }

    var getTemplate = function (documentHtml) {
        var tmp = documentHtml.split('<html');
        var templateHtml = tmp[0]+
            '<html{html_attributes}>\n' +
            '\t<head{head_attributes}>\n' +
            '\t\t{head}\n' +
            '\t</head>\n'+
            '\t<body{body_attributes}>\n' +
            '\t\t{body}\n' +
            '\t</body>\n'+
            '</html>'

        return templateHtml;
    };

    var getPreviewFrameHtml = function (templateHtml) {

    };

    return {
        getHead: getHead,
        getBody: getBody,
        getCss: getCss,
        getTemplate: getTemplate,
        mergeDocument: getPreviewFrameHtml,
    };

})(jQuery);