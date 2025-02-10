/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var contentHelper = (function($){

    var blockUid = 0;

    var previewFrame;

    var decorateBody = function(htmlSource) {
        if (htmlSource.indexOf('<div class="scroll-wrapper">') > -1) {
            return htmlSource;
        }

        htmlSource = addHelpers(htmlSource);
        htmlSource = prepareTagRepeatable(htmlSource);
        htmlSource = prepareTagEdit(htmlSource);
        htmlSource = prepareTagBgColor(htmlSource);
        htmlSource = prepareTagColor(htmlSource);
        htmlSource = addTagsAutomaticaly($('<div>'+htmlSource+'</div>')[0]);
        return $(htmlSource).html();
    };

    var decorateBlock = function (blockHtml) {

        blockHtml = prepareTagRepeatable(blockHtml);
        blockHtml = prepareTagEdit(blockHtml);
        blockHtml = prepareTagBgColor(blockHtml);
        blockHtml = prepareTagColor(blockHtml);
        blockHtml = addTagsAutomaticaly($('<div>'+blockHtml+'</div>')[0]);
        return $(blockHtml).html();
    };

    var cleanContent = function (htmlSource) {
        //htmlSource = removeHelpers(htmlSource);

        var element = $('<div>'+htmlSource+'</div>');
        element.find('[data-block-wrapped]').each(function () {
            var blockInnerHtml = $(this).find('td').html();
            element.find('#'+$(this).attr('id')).replaceWith(blockInnerHtml);
        });

        element.find('link[mt-css-helper="1"]').remove();
        element.find('[data-group-background-color]').removeAttr('data-group-background-color');
        element.find('[data-group-color]').removeAttr('data-group-color');
        element.find('.block-action').remove();
        element.find('[contenteditable]').removeAttr('contenteditable');
        element.find('#sortable_content').replaceWith($(element.find('#sortable_content').html()));


        //console.log(element.find('*[data-block]').length);

        element.find('.scroll-wrapper').replaceWith(element.find('.scroll-wrapper').html());
        element.find('.empty-placeholder').replaceWith('<div mtag_placeholder="1"></div>');


        var content = element.html();

        if (content.indexOf('data-block-wrapped') != -1) {
           console.log('content wasnt clear');
        }

        return element.html();
    };

    var addHelpers = function (htmlSource) {
        var previewFrame = $('iframe#preview-frame');

        if (previewFrame.contents().find('.scroll-wrapper').length == 0) {
            htmlSource = '<div class="scroll-wrapper">' + htmlSource + '</div>';
        }

        var element = $(htmlSource);
        if (previewFrame.contents().find('#sortable_content').length == 0) {
            var repeatableElements = element.find('*[data-repeatable]');
            repeatableElements.wrapAll('<div id="sortable_content"></div>');
        }

        if (element.find('#sortable_content').length == 0) {
            element.find('div[mtag_placeholder]').replaceWith('<div id="sortable_content"></div>');
        }

        return toHtml(element);
    }

    var prepareTagRepeatable = function (html) {
        var element = $('<div></div>').append(html);
        var repeatableElements = element.find('*[data-repeatable]');
        repeatableElements.each(function (e) {
            var blockHtml = toHtml($(this).clone());
            var wrapedHtml = wrapBlock(blockHtml);
            $(this).replaceWith($(wrapedHtml));
        });

        return element.html();
    };

    var prepareTagEdit = function (html) {
        var element = $('<div></div>').append(html);
        element.find('*[mtag_edit]').attr('contenteditable', true);
        return element.html();
    };

    var prepareTagBgColor = function (html) {
        var element = $('<div></div>').append(html);
        var list = element.find('*[data-bgcolor]');
        list.each(function () {
            $(this).attr('data-group-background-color', $(this).attr('data-bgcolor'));
        });
        return element.html();
    };

    var prepareTagColor = function (html) {
        var element = $('<div></div>').append(html);
        var list = element.find('*[data-color]');
        list.each(function () {
            $(this).attr('data-group-color', $(this).attr('data-color'));
        });
        return element.html();
    };

    var addTagsAutomaticaly = function(context) {

        var nodeList = getNodes(context, 'color:');
        groupColor(nodeList, 'color', function (node) {
            return $(node);
        });

        var nodeList = getTextNodesIn(context);
        $.each(nodeList, function (key, node) {
            if (typeof $(node).parent().attr('contenteditable') !== typeof undefined) {
                return;
            }

            var parentNode = $(node).parent();
            parentNode.attr('contenteditable', true);
        });

        $(context).find('[bgcolor]').each(function () {
            $(this).css('background-color', $(this).attr('bgcolor'));
        });

        var nodeList = getNotTransparentNodes(context);
        groupColor(nodeList, 'background-color', function (node) {
            return $(node);
        });

        return context;
    };

    /**
     * Group colors
     *
     * @param nodeList
     * @param cssAttribute
     * @param getElement
     */
    var groupColor = function (nodeList, cssAttribute, getElement) {
        var colorGroup = {};
        var i = 1;

        /**
         * Load already existing groups
         */
        $('*[data-group-'+cssAttribute+']').each(function () {
            var elm = $(this);
            var colorCode = toHex(elm.css(cssAttribute)).toLowerCase();
            if (!colorGroup[colorCode]) {
                colorGroup[colorCode] = elm.attr('data-group-'+cssAttribute);
                i++;
            }
        });

        /**
         * Group elements
         */
        $.each(nodeList, function (key, node) {
            var elm = getElement(node);
            var colorCode = toHex(elm.css(cssAttribute)).toLowerCase();
            if (colorCode.length != 7) {
                return;
            }

            if (!colorGroup[colorCode]) {

                colorGroup[colorCode] = 'Color: #'+i;
                i++;
            }

            if (elm.attr('data-group-'+cssAttribute) == undefined) {
                elm.attr('data-group-'+cssAttribute, colorGroup[colorCode]);
            }
        });

        $.each([
            '.mteditor-content-helper-text',
            '.mteditor-content-helper-link',
            '.mteditor-content-helper-img',
            '.mteditor-content-helper-selected',
            '.editor-helper-active',
            '.editor-selected-link',
            '.block-action'
        ], function (index, selector) {
            $(selector+ ' '+'[data-group-'+cssAttribute+']').removeAttr('data-group-'+cssAttribute);
        });
    };

    var getTextNodesIn = function (node, includeWhitespaceNodes) {
        var textNodes = [], nonWhitespaceMatcher = /\S/;
        function getTextNodes(node) {
            if (node.nodeType == 3) {
                if (includeWhitespaceNodes || nonWhitespaceMatcher.test(node.nodeValue)) {
                    textNodes.push(node);
                }
            } else {
                for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                    getTextNodes(node.childNodes[i]);
                }
            }
        }

        getTextNodes(node);
        return textNodes;
    };

    var getNotTransparentNodes = function (node, includeWhitespaceNodes) {
        var textNodes = [], nonWhitespaceMatcher = /\S/;
        function getTextNodes(node) {
            if ($(node) && node.nodeType == 1) {
                var bgColor = '';
                var styleAttribute = $(node).attr('style');

                if (styleAttribute != undefined && (
                       styleAttribute.indexOf("background-color") != -1
                    || styleAttribute.indexOf("background") != -1)
                ) {
                    bgColor = $(node).css('background-color');
                } else if ($(node).attr('bgcolor') != undefined) {
                    bgColor = $(node).attr('bgcolor');
                }

                if (bgColor != '' && bgColor != 'rgba(0, 0, 0, 0)') {
                    textNodes.push(node);
                }
            }

            if (node.childNodes.length > 0) {
                for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                    getTextNodes(node.childNodes[i]);
                }
            }
        }

        getTextNodes(node);
        return textNodes;
    };

    var getNodes = function (node, styleAttributeCode, includeWhitespaceNodes) {
        var textNodes = [], nonWhitespaceMatcher = /\S/;
        function getOther(node) {
            if ($(node) && node.nodeType == 1) {
                var bgColor = '';
                var styleAttribute = $(node).attr('style');

                if (styleAttribute != undefined && styleAttribute.indexOf(styleAttributeCode) != -1 &&
                    styleAttribute[styleAttribute.indexOf(styleAttributeCode)-1] != '-') {
                    bgColor = $(node).css(styleAttributeCode);
                    textNodes.push(node);
                }
            }

            if (node.childNodes.length > 0) {
                for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                    getOther(node.childNodes[i]);
                }
            }
        }

        getOther(node);
        return textNodes;
    };

    var wrapBlock = function(blockHtml) {
        if (blockHtml.indexOf('data-block-wrapped="1"') != -1 || blockHtml.length < 10) {
            return blockHtml;
        }

        blockUid++;
        var content = '<table data-block-wrapped="1" id="block_'+blockUid+'" data-block="'+blockUid+'" width="100%" border="0" cellpadding="0" cellspaceing="0"><tr><td>'+blockHtml+'</td></tr></table>';
        $('#mteditor_tmp').html(content);
        var blockActionHtml = $('#block-action').clone().attr('class', 'block-action').removeAttr('id');
        $('#mteditor_tmp table td:first').prepend(blockActionHtml);
        return $('#mteditor_tmp').html();
    };

    var toHtml = function (element) {
        return mtEditor.toHtml(element);
    }

    var toHex = function(color) {
       return mtEditor.toHex(color);
    };

    return {
        decorateBody: decorateBody,
        decorateBlock: decorateBlock,
        cleanContent: cleanContent,
        wrapBlock: wrapBlock
    };
})(jQuery);