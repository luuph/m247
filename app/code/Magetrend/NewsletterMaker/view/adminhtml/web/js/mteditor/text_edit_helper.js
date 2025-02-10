/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var textEditHelper = (function($){
    var config = {
        'classes': {
            'helperLink': 'mteditor-content-helper-link',
            'helperImg': 'mteditor-content-helper-img',
            'helperText': 'mteditor-content-helper-text',
            'helperSelected': 'mteditor-content-helper-selected',
            'helperContentImage': 'mteditor-content-helper-content-image'
        }
    };

    var box = false;

    var cusotm;

    var previewFrame;

    var init = function() {
        //$('.helper-text .select-selected').remove();
        //$('.helper-text .select-items').remove();
        previewFrame = $('iframe#preview-frame');
        removeHelper();
        initEvent();
        initEditEvent();
    };

    var loadMissingFonts = function () {
        var options = {};
        $('select[name="font-family"] option').each(function () {
            options[$(this).text()] = 1;
        });

        previewFrame.contents().find('[contenteditable]').each(function () {
            var font = $(this).css('font-family').split('"').join('').split("'").join('');
            if (!options[font]) {
                $('select[name="font-family"]').append($('<option value="'+font+'">'+font+'</option>'));
                options[font] = 1;
            }
        });
    };

    var initEvent  = function() {
        var frameContents = previewFrame.contents();
        frameContents.find('body').unbind('click').click(function(e) {
            e.preventDefault();
            mtEditor.log('click on content');
            var elm = $(e.target);
            if (elm.is('img')) {
                hide();
                addImgHelper(elm);
                mtEditor.openImageTools();
                show(elm, 'helper-image');

            } else if (elm.attr('contenteditable') == 'true') {
                hide();
                addTextHelper(elm);
                mtEditor.openStyleTools();
                initTextHelper(elm);
                show(elm, 'helper-text');
            } else if (elm.parents('*[contenteditable="true"]').length > 0) {
                hide();
                var parentElm = elm.parents('*[contenteditable="true"]');
                addTextHelper(parentElm);
                initTextHelper(parentElm);
                mtEditor.openStyleTools();
                show(parentElm, 'helper-text');
            } else {
                $('a[data-selector="edit-style"]').trigger('click');
                hide();
            }
        });

        $(document).unbind('click').on( "click", function( event ) {
            if (
                (
                $(event.target).closest("#email").length == 0
                && !$(event.target).closest(".edit-image").length
                && !$(event.target).parents('#editor_helper').length
                && !$(event.target).parents('.popup-content').length
                && !$(event.target).parents('#colorpicker').length
                && !($('.'+config.classes.helperImg).length > 0 && $(event.target).is('a[data-selector="edit-image"]'))
                )
            ||
                $(event.target).closest('.block-action').length > 0
            ) {
                hide();
            }
        });

        $('.bock-action').mouseover(function(){
            hide();
        });
    };

    var show = function(elm, editorClass) {

        var helperElm = $('#editor_helper');
        helperElm.hide();
        $('#editor_helper .helper').hide();
        $('#editor_helper .'+editorClass).show();

        if (editorClass == 'helper-image') {
            $('#editor_helper .'+editorClass+ ' a[data-image-editable="true"]').parent('li').show();
        } else {
            $('#editor_helper .'+editorClass+ ' a[data-image-editable="true"]').parent('li').hide();
        }

        helperElm.css({
            top: -99999+'px'
        }).show();

        updateHelperPosition(helperElm, elm);
        previewFrame.contents().find( ".scroll-wrapper" ).unbind('scroll').scroll(function () {
            updateHelperPosition(helperElm, elm);
        });


    };

    var updateHelperPosition = function (helper, elm) {
        var iframePos = previewFrame.offset();
        var pos = elm.offset();
        pos.left = pos.left + iframePos.left;
        pos.top = pos.top + iframePos.top;
        var posY = pos.top - $(document).scrollTop();
        var elmHeight = elm.height();


        var height = helper.height();
        var top = 0;
        if (posY - height - 30 < 0) {
            top = pos.top + height + elmHeight + 10;
        } else {
            top = pos.top - height - 30;
        }

        helper.css({
            top: top +'px',
            left: pos.left+'px'
        });
    };

    var initEditEvent = function() {
        $('a[data-action="bold"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doBoldLink();
            } else if(isActiveText()) {
                doBoldText();
            }
        });

        $('a[data-action="italic"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doItalicLink();
            } else if(isActiveText()) {
                doItalicText();
            }
        });

        $('a[data-action="underline"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doUnderlineLink();
            } else if(isActiveText()) {
                doUnderlineText();
            }
        });

        $('a[data-action="align-left"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('left');
            } else if(isActiveText()) {
                doAlignText('left');
            } else if(isActiveImg()) {
                doAlignImage('left');
            }

        });
        $('a[data-action="align-right"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('right');
            } else if(isActiveText()) {
                doAlignText('right');
            } else if(isActiveImg()) {
                doAlignImage('right');
            }

        });
        $('a[data-action="align-center"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('center');
            } else if(isActiveText()) {
                doAlignText('center');
            } else if(isActiveImg()) {
                doAlignImage('center');
            }

        });

        $('a[data-action="align-justify"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('justify');
            } else if(isActiveText()) {
                doAlignText('justify');
            } else if(isActiveImg()) {
                doAlignImage('justify');
            }

        });

        $('a[data-action="link"]').unbind( "click" ).click(function(e) {
            e.preventDefault();

            if (
                $(this).parents('.block-action').length  > 0
                && elm.data('action')!= 'link'
            ) {
                return true;
            }

            if (isActiveLink()) {
                doLink('link');
            } else if(isActiveText()) {
                doLink('text');
            } else if(isActiveImg()) {
                doLink('img');
            }
        });

        $('a[data-action="var"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveText()) {
                doVar();
            }
        });

        var editor;
        $('a[data-action="html"]').unbind( "click" ).click(function(e) {
            sourceEditor.show();
            sourceEditor.updateEditorScroolPosition(e);
        });

        $('a[data-action="image"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveText()) {
                initContentImageHelper();
                mtEditor.openImageTools();

            }
        });

        $('a[data-action="remove-image"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveImg()) {
                removeImage();
            }
        });

        $('#editor_helper select[name="font-family"]').unbind('change').change(function () {
            changeFontFamily($('.helper-text select[name="font-family"]').val());
        });

        $('#editor_helper input[name="font-size"]').unbind('change').change(function () {
            doChangeFontSize($(this).val());
        });

        $('#editor_helper input[name="color"]').unbind('change').change(function () {
            doChangeColor($(this).val());
        });
    };

    var removeImage = function () {
        previewFrame.contents().find('.'+config.classes.helperImg).remove();
        mtEditor.openStyleTools();
        hide();
    };

    var replaceSelectedContent = function (html) {
        var range, html;
        var iframe = document.getElementById('preview-frame');
        var idoc = iframe.contentDocument || iframe.contentWindow.document;

        if (idoc.getSelection && idoc.getSelection().getRangeAt) {
            range = idoc.getSelection().getRangeAt(0);
            range.deleteContents();
            var div = document.createElement("div");
            div.innerHTML = html;
            var frag = document.createDocumentFragment(), child;
            while ( (child = div.firstChild) ) {
                frag.appendChild(child);
            }
            range.insertNode(frag);
        } else if (idoc.selection && idoc.selection.createRange) {
            range = idoc.selection.createRange();
            range.pasteHTML(html);
        }
    //    initEditEvent();
    };

    var hide = function() {
        var previewFrameContents = previewFrame.contents();
        $('.editor-helper-active').removeClass('editor-helper-active');
        previewFrameContents.find('.mteditor-content-helper-selected').replaceWith(previewFrameContents.find('.mteditor-content-helper-selected').html());
        $('#editor_helper').hide();
        $('#editor_helper .helper').hide();
        removeHelper();
    };

    var getSelectionHtml = function () {
        var html = "";
        var iframe = document.getElementById('preview-frame');
        var idoc = iframe.contentDocument || iframe.contentWindow.document;

        if (typeof idoc.getSelection != "undefined") {
            var sel = idoc.getSelection();
            if (sel.rangeCount) {
                var container = document.createElement("span");
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
                html = container.innerHTML;
            }
        } else if (typeof document.selection != "undefined") {
            if (document.selection.type == "Text") {
                html = document.selection.createRange().htmlText;
            }
        }
        return html;
    };

    var initLinkForm = function(elmType) {

        $('#esns_box_content .form-group').show();
        if (elmType == 'link') {
            var link = previewFrame.contents().find('.'+config.classes.helperText);
            $('input[name="editor_link_href"]').val(link.attr('href'));
            $('input[name="editor_link_title"]').val($.trim(link.html()));
            if (link.data('disable-remove') == 1) {
                $('#esns_box_content *[data-action="0"]').hide();
            } else {
                $('#esns_box_content *[data-action="0"]').show();
            }
        } else if (elmType == 'text') {
            $('input[name="editor_link_title"]').val(previewFrame.contents().find('.'+config.classes.helperSelected).html());
        } else if (elmType == 'img') {
            var elm = previewFrame.contents().find('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                $('input[name="editor_link_href"]').val(elm.parents('a').attr('href'));
            }
            $('#esns_box_content .form-group.link-title').hide();
        }
    };

    var updateLink = function(type) {

        var href = $('#esns_box_content input[name="editor_link_href"]').val();
        var val = $('#esns_box_content input[name="editor_link_title"]').val();

        if (type == 'text') {
            previewFrame.contents().find('.'+config.classes.helperSelected).replaceWith('<a contenteditable="true" href="'+href+'">'+val+'</a>');
        } else if (type == 'link') {
            previewFrame.contents().find('.'+config.classes.helperText).html(val).attr('href', href);
        } else if (type == 'img') {
            var elm = previewFrame.contents().find('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                elm.parent().attr('href', href);
            } else {
                elm.replaceWith('<a contenteditable="true" href="'+href+'">'+elm.wrap("<span></span>").parent().html()+'</a>');
            }
            mtEditor.initImage();
        }

    };

    var removeLink = function(type) {
        if (type == 'link') {
            previewFrame.contents().find('.'+config.classes.helperText)
                .parent()
                .attr('contenteditable', 'true');
            previewFrame.contents().find('.'+config.classes.helperText).replaceWith(previewFrame.contents().find('.'+config.classes.helperText).html())
        } else if (type == 'img') {
            var elm = $('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                elm.parent().replaceWith(elm.parent().html());
                mtEditor.initImage();
            }
        }
    };

    var initContentHelper = function() {
        if (previewFrame.contents().find('.'+config.classes.helperSelected).length  ==  0) {
            replaceSelectedContent('<span class="'+config.classes.helperSelected+'">'+getSelectionHtml()+'</span>');
        }
    };

    var initContentImageHelper = function() {
        if (previewFrame.contents().find('.'+config.classes.helperContentImage).length > 0){
            previewFrame.contents().find('.'+config.classes.helperContentImage).remove();
        }
        replaceSelectedContent('<span class="'+config.classes.helperContentImage+'" style="display: none;"></span>');
    };

    var addLinkHelper = function(elm) {
        elm.addClass(config.classes.helperLink);
    };

    var addImgHelper = function(elm) {
        elm.addClass(config.classes.helperImg);
    };

    var addTextHelper = function(elm) {
        elm.addClass(config.classes.helperText);
    };

    var isActiveLink = function() {
        var previewContents = previewFrame.contents();
        if (previewContents.find('.' + config.classes.helperLink).length > 0) {
            return true;
        }

        var activeTextElement = previewContents.find('.' + config.classes.helperText);
        if (activeTextElement.length > 0 && activeTextElement.is('a')) {
            return true;
        }

        return false
    }

    var isActiveImg = function() {
        return  previewFrame.contents().find('.'+config.classes.helperImg).length > 0;
    };

    var isActiveText = function() {
        return previewFrame.contents().find('.'+config.classes.helperText).length > 0;
    };

    var removeHelper = function() {
        mtEditor.log('remove all helpers');
        var previewFrameContents = previewFrame.contents();
        previewFrameContents.find('.'+config.classes.helperImg).removeClass(config.classes.helperImg);
        previewFrameContents.find('.'+config.classes.helperLink).removeClass(config.classes.helperLink);
        previewFrameContents.find('.'+config.classes.helperText).removeClass(config.classes.helperText);
        previewFrameContents.find('.'+config.classes.helperContentImage).remove();
    };

    var doBoldLink = function () {
        var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
        if (currentElm.css('font-weight') == 700) {
            currentElm.css('font-weight','');
        } else {
            currentElm.css('font-weight','700');
        }
    };

    var doBoldText = function () {
        var selectedContent = getSelectionHtml();
        if (!selectedContent) {
            var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
            if (currentElm.css('font-weight') == 700) {
                currentElm.css('font-weight','');
            } else {
                currentElm.css('font-weight','700');
            }
        } else {
            if (selectedContent.split('<b>').length > 1) {
                selectedContent = selectedContent.replace('<b>','').replace('</b>','');
            } else {
                selectedContent = '<b>'+selectedContent+'</b>';
            }
            replaceSelectedContent(selectedContent);
        }
    };

    var doItalicLink = function () {
        var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
        if (currentElm.css('font-style') == 'italic') {
            currentElm.css('font-style','');
        } else {
            currentElm.css('font-style','italic');
        }
    };

    var doItalicText = function () {
        var selectedContent = getSelectionHtml();
        if (!selectedContent) {
            var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
            if (currentElm.css('font-style') == 'italic') {
                currentElm.css('font-style','');
            } else {
                currentElm.css('font-style','italic');
            }
        } else {
            if (selectedContent.split('<i>').length > 1) {
                selectedContent = selectedContent.replace('<i>','').replace('</i>','');
            } else {
                selectedContent = '<i>'+selectedContent+'</i>';
            }
            replaceSelectedContent(selectedContent);
        }
    };

    var doUnderlineLink = function () {
        var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
        if (currentElm.css('text-decoration').indexOf('underline') != -1) {
            currentElm.css('text-decoration', 'none');
        } else {
            currentElm.css('text-decoration', 'underline');
        }
    };

    var doUnderlineText = function () {
        var selectedContent = getSelectionHtml();
        if (!selectedContent) {
            var currentElm = previewFrame.contents().find('.'+config.classes.helperText);
            if (currentElm.css('text-decoration').indexOf('underline') != -1) {
                currentElm.css('text-decoration', 'none');
            } else {
                currentElm.css('text-decoration', 'underline');
            }
        } else {
            if (selectedContent.split('<u>').length > 1) {
                selectedContent = selectedContent.replace('<u>','').replace('</u>','');
            } else {
                selectedContent = '<u>'+selectedContent+'</u>';
            }
            replaceSelectedContent(selectedContent);
        }
    };

    var doAlignLink = function (align) {
        previewFrame.contents().find('.'+config.classes.helperText).css('text-align', align);
    };

    var doAlignText = function (align) {
        previewFrame.contents().find('.'+config.classes.helperText).css('text-align', align);
    };

    var doAlignImage = function (align) {
        previewFrame.contents().find('.'+config.classes.helperImg).css('text-align', align).attr('align', align);
    };

    var changeFontFamily = function (fontFamily) {
        previewFrame.contents().find('.'+config.classes.helperText).css('font-family', fontFamily);
    }

    var doChangeFontSize = function (fontSize) {
        previewFrame.contents().find('.'+config.classes.helperText).css('font-size', fontSize);
    }

    var doChangeColor = function (color) {
        previewFrame.contents().find('.'+config.classes.helperText).css('color', color);
    }

    var doLink = function (elmType) {
        if (elmType == 'text') {
            initContentHelper();
        }
        popup.content({
            contentSelector: '#edit_link_form'
        }, function(){
           initLinkForm(elmType);
        }, function(){
            updateLink(elmType);
            hide();
        }, function(){
            removeLink(elmType);
            hide();
            removeHelper();
        });
    };

    var doVar = function () {

        initContentHelper();
        popup.content({
            contentSelector: '#edit_var_form'
        }, function(){

        }, function(){
            insertVar();
          //  hide();
        }, function(){
         //   removeLink(elmType);
          //  hide();
          //  removeHelper();
        });
    };

    var insertVar = function() {
        var helper =  previewFrame.contents().find('.mteditor-content-helper-selected');
        if (helper.length == 0) {
            return;
        }
        var variable = $('#esns_box_layer select[name="editor_var"]').val();
        $.each(mtEditor.config.vars, function (index, option) {
            if (variable == option.label) {
                helper.replaceWith(option.value);
            }
        });
    };

    var initTextHelper = function (elm) {
        $('.helper-text input[name="font-size"]').val(elm.css('font-size'));
        var color = mtEditor.toHex(elm.css('color'));
        var colorField = $('.helper-text input[name="color"]');
        colorField.val(color).addClass('active');
        colorPicker.updateColor(color);
        colorField.removeClass('active');

        $('#fontFamily').val(elm.css('font-family').split('"').join('').split("'").join(''));
        $('#editor_helper .customSelect').remove();
        $('#editor_helper .hasCustomSelect').removeClass('hasCustomSelect').removeAttr('style');
        $('#fontFamily').customSelect();
    };

    return {
        init: init,
        config: config,
        initLinkForm: initLinkForm,
        updateLink: updateLink,
        show: show,
        replaceSelectedContent: replaceSelectedContent,
        hide: hide
    };
})(jQuery);
