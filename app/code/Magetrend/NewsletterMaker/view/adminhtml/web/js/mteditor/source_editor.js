/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var sourceEditor = (function() {

    var config = {};

    var previewFrame;

    var htmlEditor;

    var cssEditor;

    var isPreviewReloadDisabled = false;

    var sourceSelector = '#source_panel';

    var cssPanelSelector = '.source-panel-window-css';

    var sourcePanel;

    var cssPanel;

    var editorHeight = 0;

    var cssWidth = 0;

    var fullSource;

    var activeLine = 0;

    var activeCh = 0;

    var disableCssChangeEvent = true;

    var disableHtmlChangeEvent = true;

    /**
     * body|block|text
     * @type {string}
     */
    var mode = 'body';

    var init = function(settings) {
        $.extend(config, settings );
        previewFrame = $('iframe#preview-frame');
        setup();
    };

    var setup = function() {

        editorHeight = parseInt($('#page-wrapper').height()) * 0.6;
        cssWidth = parseInt($('#page-wrapper').width()) * 0.4;
        sourcePanel = $(sourceSelector);
        cssPanel = $(cssPanelSelector);

        $('#source_editor_html').val(config.document);
        htmlEditor = CodeMirror.fromTextArea(document.getElementById("source_editor_html"), {
            mode: 'htmlmixed',
            lineNumbers: true,
            selectionPointer: false,
            gutters: ["CodeMirror-linenumbers"],
            inputStyle: 'contenteditable'
        });

        $('#source_editor_css').val(templateHelper.getCss(config.document));
        cssEditor = CodeMirror.fromTextArea(document.getElementById("source_editor_css"), {
            mode: 'css',
            lineNumbers: true,
            selectionPointer: false,
            gutters: ["CodeMirror-linenumbers"],
            inputStyle: 'contenteditable'
        });

        var element = $('.source-panel-resize-up-down');
        element.resizable({
            handles: {
                'n': '#ngrip',
            },

            start: function (event, ui) {
                $('.iframe-mask').remove();
                $('<div class="iframe-mask"></div>').insertAfter(previewFrame);
                $('.iframe-mask').css({
                    width: previewFrame.css('width'),
                    height: previewFrame.css('height'),
                    top: previewFrame.offset().top,
                    left: previewFrame.offset().left,
                    position: 'absolute'
                })
            },

            stop: function (event, ui) {
                $('.iframe-mask').remove();
            },

            resize: function (event, ui) {
                editorHeight = ui.size.height;
                updateLayoutByEditorSize(ui.size.height);
            }
        });

        var element = $('.source-panel-window-css');
        element.resizable({
            handles: {
                'w': '#wgrip',
            },
            resize: function (event, ui) {
                updateSourcePanelSize(ui.size.width);
                cssWidth = ui.size.width;
            }
        });

        setupCloseButton();
        setupCssPanelTab();

        $(window).resize(function() {
            updateLayoutByEditorSize(editorHeight);
            updateSourcePanelSize(cssWidth);
        });

        htmlEditor.on('blur',function(e){
            disableHtmlChangeEvent = true;
        });

        htmlEditor.on('focus',function(){
            disableHtmlChangeEvent = false;
        });

        cssEditor.on('blur',function(e){
            disableCssChangeEvent = true;
        });

        cssEditor.on('focus',function(){
            disableCssChangeEvent = false;
        });

        previewFrame.contents().find('html').mouseup(function (e) {
            sourceEditor.updateEditorScroolPosition(e);
        });

        htmlEditor.on('change',function(){
            if (disableHtmlChangeEvent) {
                return;
            }

            mtEditor.saveBodyTop();

            var previewFrameContents = previewFrame.contents();
            var newHtmlSource = htmlEditor.getValue();
            var newCssSource = templateHelper.getCss(newHtmlSource);

            previewFrameContents.find('body').html(templateHelper.getBody(newHtmlSource));
            previewFrameContents.find('head').html(templateHelper.getHead(newHtmlSource));

            var pos = cssEditor.getScrollInfo();
            cssEditor.setValue(newCssSource);
            cssEditor.scrollTo(pos.left, pos.top);

            var styleBlock = previewFrameContents.find('style[data-css]');
            if (styleBlock.length == 0) {
                styleBlock =  previewFrameContents.find('style');
            }
            styleBlock.html(cssEditor.getValue());

            mtEditor.restoreBodyTop();
        });

        cssEditor.on('change',function(e){
            if (disableCssChangeEvent) {
                return;
            }
            disableHtmlsChangeEvent = true;
            var styleBlock = previewFrame.contents().find('style[data-css]');
            if (styleBlock.length == 0) {
                styleBlock =  previewFrame.contents().find('style');
                styleBlock.attr('data-css', 1);
            }
            var newCss = cssEditor.getValue();
            styleBlock.html(cssEditor.getValue());
            var htmlSource = htmlEditor.getValue();
            var pos = htmlEditor.getScrollInfo();

            var cssElement = templateHelper.getCss(htmlSource, true);
            var newCssElement = $('<div></div>').append($(cssElement).html(newCss)).html();
            var newHtmlEditorValue = htmlSource.replace(cssElement, newCssElement);
            htmlEditor.setValue(newHtmlEditorValue);
            htmlEditor.scrollTo(pos.left, pos.top);
        });

        cssEditor.on('focus',function(){
            disableHtmlsChangeEvent = true;
            var styleBlock = previewFrame.contents().find('style[data-css]');
            if (styleBlock.length == 0) {
                styleBlock =  previewFrame.contents().find('style');
                styleBlock.attr('data-css', 1);
            }
            var pos = cssEditor.getScrollInfo();
            cssEditor.setValue(styleBlock.html());
            cssEditor.scrollTo(pos.left, pos.top);
            disableHtmlsChangeEvent = false;
        });
    };

    var updateEditorScroolPosition = function (e) {
        var htmlSource = htmlEditor.getValue();
        var targetElement = previewFrame.contents().find(e.target);

        var findInContent = '';
        if (targetElement.is('img')) {
            findInContent = targetElement.attr('src');
        } else if (targetElement.attr('contenteditable')) {
            findInContent = targetElement.html();
        } else if (previewFrame.contents().find('.mteditor-content-helper-text').length > 0) {
            findInContent = previewFrame.contents().find('.mteditor-content-helper-text').html();
        } else if (previewFrame.contents().find('.active-block').length > 0) {
            findInContent = previewFrame.contents().find('.active-block [data-block]').html();
        }

        if (findInContent == '' || htmlSource.indexOf(findInContent) === -1) {
            return;
        }

        var lines = htmlSource.slice(0, htmlSource.indexOf(findInContent)).split("\n");
        activeLine = lines.length-1;
        activeCh = lines[activeLine].length;
        var pos = htmlEditor.charCoords({line: activeLine, ch: activeCh}, "local");
        htmlEditor.scrollTo(pos.left, pos.top);
    };

    var setupCloseButton = function () {
        $('.source-tab-close a').click(function () {
            hide();
        });
    };

    var setupCssPanelTab = function () {
        $('.source-tab-css').click(function () {
            if ($(this).hasClass('active')) {
                hideCssPanel();
                $(this).removeClass('active');
            } else {
                showCssPanel();
                $(this).addClass('active');
            }
        });
    };

    var showCssPanel = function () {
        cssPanel.show();
        updateSourcePanelSize(cssWidth);
    };

    var hideCssPanel = function () {
        cssPanel.hide();
        updateSourcePanelSize(0);
    };

    var syncWithPreview = function () {
        var previewFrameContent = previewFrame.contents();
        var newBodyHtml = contentHelper.cleanContent(previewFrameContent.find('body').clone().html());
        var currentSource = htmlEditor.getValue();
        var newHtml = currentSource.replace(templateHelper.getBody(currentSource), newBodyHtml);
        updateEditorValue(htmlEditor, newHtml);
    };

    var updateEditorValue = function (editor, value) {
        if (value != editor.getValue()) {
            var pos = editor.getScrollInfo();
            editor.setValue(value);
            htmlEditor.scrollTo(pos.left, pos.top);
        }
    };

    var updateEditorSize = function(editor, element)
    {
        editor.setSize(element.width(), element.height());
        editor.refresh();
    }

    var updateLayoutByEditorSize = function (panelHeight) {

        if (!sourcePanel.is(':visible')) {
            previewFrame.css('padding-bottom', 0);
            return;
        }

        var fullHeight = $('#page-wrapper').height();
        if (panelHeight > fullHeight * 0.9) {
            panelHeight = fullHeight * 0.9;
        }

        $('.source-panel-window-html').height(panelHeight);
        $('.source-panel-window-css').height(panelHeight);
        $('.source-panel-resize-up-down.ui-resizable').height(panelHeight+25).css({
            top: (panelHeight*-1)+'px'
        });

        previewFrame.css('padding-bottom', (panelHeight + 25)+'px');
        updateEditorSize(htmlEditor, $('.source-panel-window-html'));
        updateEditorSize(cssEditor, $('.source-panel-window-css'));

        $('#source_panel').css({
            width: previewFrame.width()+'px',
            bottom: (panelHeight * -1) +'px'
        });
    }

    var updateSourcePanelSize = function (cssPanelWidth) {
        if (!cssPanel.is(':visible')) {
            cssPanelWidth = 0;
        }

        var fullWidth = $('#source_panel').width();
        var htmlPanelWidth = fullWidth - cssPanelWidth;

        if (cssPanelWidth > 0 && cssPanelWidth > fullWidth * 0.9) {
            cssPanelWidth = fullWidth * 0.9;
            htmlPanelWidth = fullWidth * 0.1;
        }

        if (cssPanelWidth > 0 && htmlPanelWidth > fullWidth * 0.9) {
            htmlPanelWidth = fullWidth * 0.9;
            cssPanelWidth = fullWidth * 0.1;
        }

        var gripSize = parseInt($('.source-panel-window-css .wgrip').width());
        cssPanelWidth -= gripSize;

        $('.source-panel-window-html').width(htmlPanelWidth);
        $('.source-panel-window-css').width(cssPanelWidth).css({
            right: 0,
            left: 'auto'
        });

        updateEditorSize(htmlEditor, $('.source-panel-window-html'));
        updateEditorSize(cssEditor, $('.source-panel-window-css'));
    }

    var getHtml = function () {
        syncWithPreview();
        return htmlEditor.getValue();
    };

    var getCss = function () {
        return cssEditor.getValue();
    };

    var getBody = function () {
        return templateHelper.getBody(getHtml());
    };

    var getHead = function () {
        return templateHelper.getHead(getHtml());
    };

    var toggle = function() {
        if (sourcePanel.is(':visible')) {
            hide();
        } else {
            show();
        }
    };

    var show = function () {
        sourcePanel.show();
        updateLayoutByEditorSize(editorHeight);
        updateSourcePanelSize(cssWidth);
    };

    var hide = function () {
        sourcePanel.hide();
        updateLayoutByEditorSize(0);
    };

    return {
        init: init,
        syncWithPreview: syncWithPreview,
        updateEditorScroolPosition: updateEditorScroolPosition,
        getHtml: getHtml,
        getCss: getCss,
        getHead: getHead,
        getBody: getBody,
        toggle: toggle,
        show: show
    };
})(jQuery);