/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

var mtEditor = (function($) {

    var isSingleBlockTemplate = false;

    var config = {
        log: 0,
        templateMaxWidth: 600,
        minWindowHeight: 600,
        data: {},
        fontFamilyOptions: {},
        template_id: 0,
        document: {
            css: '',
            container: ''
        }
    };

    var scroll = {
        up: false,
        down: false
    };

    var mode;

    var previewFrame;

    var previewFrameTop = 0;

    var removedBlockList = {};

    var init = function(options) {
        $.extend(config, options);

       if ($.cookie('mteditor_log') == '1') {
           config.log = 1;
        }

        initLayout();
        initPopup();

        if (config.template_id == 0) {
            initNewTemplate();
            return false;
        }

        initBlock();
        initPreviewFrame();

        sourceEditor.init(config);
        enterToPreviewMode();
        initEditorEvent();

        initFileUpload();
        initPlaceholder();

        loadMissingFonts();
        loadImageList();

        saveHelper.init({
            document: config.document,
            template_id: config.template_id,
            action: {
                saveUrl: config.action.saveUrl
            },
            formKey: config.formKey
        });

        if (isSingleBlockMode()) {
            hideLayoutMenu();
        }

        preloadImages();
    };

    var initPreviewFrame = function () {
        previewFrame = $('iframe#preview-frame');
        var head = previewFrame.contents().find("head");
        var body = previewFrame.contents().find('body');
        body.html(templateHelper.getBody(config.document));
        head.html(templateHelper.getHead(config.document));
    };

    var enterToPreviewMode = function () {
        if (mode == 'preview') {
            return;
        }
        mode = 'preview';
        saveBodyTop();
        decoratePreviewFrame();
        reloadPreviewFrameEvents();
        restoreBodyTop();
    }

    var enterToSourceEditMode = function () {
        if (mode == 'source') {
            return;
        }
        mode = 'source';
        saveBodyTop();
        sourceEditor.syncWithPreview();
        restoreBodyTop();
    };

    var decoratePreviewFrame = function () {
        var head = previewFrame.contents().find("head");
        var body = previewFrame.contents().find('body');
        $.each(config.iframe.css, function (index, cssLink) {
            head.append($('<link mt-css-helper="1" rel="stylesheet" type="text/css" href="'+cssLink+'"/>'));
        });
        body.html(contentHelper.decorateBody(body.html()));

    }

    var reloadPreviewFrameEvents = function () {
        reloadDynamicContentEvent();
        initDragAndDrop();
        textEditHelper.init();
    };

    var initBlock = function() {
        if (needToRenderBlockThumbs()) {
            $('body').append($('<iframe id="tempIframe" width="100%" style="position:absolute; top: -9990px; z-index: 0"></iframe>'));
            $('#tempIframe').contents().find('head').html(templateHelper.getHead(config.origDocument));
        }
        $.each(config.data, function( index, value ) {
            var blockId = 'block_thumb_'+index;
            $('#draggable').append('<table border="0"><tr><td id="'+blockId+'"></td></tr></table>');

            if (value.image == "") {
                $('#tempIframe').contents().find('body').append('<div id="tempContainer_'+index+'" style="position: absolute; top 0; left: -99999px; width: 600px">'+value.content+'</div>');
                html2canvas(document.getElementById('tempIframe').contentWindow.document.getElementById('tempContainer_'+index), {
                    async: true,
                    allowTaint: false,
                    useCORS: true,
                    scale: 0.5,
                    logging: false
                }).then( function (canvas) {
                    var imgSource = canvas.toDataURL("image/png");
                    $('#'+blockId).html('<img  data-id="'+index+'" width="100%" src="'+imgSource+'" />');

                    sendRequest(mtEditor.config.action.saveImage, {
                        image: imgSource,
                        block_hash: value.block_hash
                    }, function () {});

                }).catch(function (error) {
                    log(error);
                });

            } else {
                $('#'+blockId).html('<img data-id="'+index+'" width="100%" src="'+value.image+'" />');
            }
        });
    };

    var needToRenderBlockThumbs = function () {
        var need = false;
        $.each(config.data, function( index, value ) {
            if (value.image == "") {
                need = true;
            }
        });
        return need;
    }

    var initNewTemplate = function() {
        var validate = function() {
            var localeCode = $('#esns_box_layer select[name="email_locale"]').val();
            var templateCode = $('#esns_box_layer select[name="email_template"]').val();
            var name = $('#esns_box_layer input[name="template_code"]').val();
            var subject = $('#esns_box_layer input[name="template_subject"]').val();
            if (localeCode && templateCode && name && subject) {
                $('#esns_box_layer button[data-action="1"]').removeAttr('disabled');
                return true;
            } else {
                $('#esns_box_layer button[data-action="1"]').attr('disabled', 'disabled');
                return false;
            }
        };

        popup.initPopup({
            contentSelector: '#init_new_template',
            disableClose: true,
            beforeOpen: function () {
                var time = new Date().getTime();
                var fileUploadUid = $('#esns_box_content #template_upload').attr('id') + '_'+ time;
                $('#esns_box_content .template_upload').attr('id', fileUploadUid);
                var buttonText = $('#'+fileUploadUid+' .mteditor_upload_button').text();

                var validate = function () {

                    if ($('#esns_box_content input[name="template_ready"]').val() != 1) {
                        return false;
                    };


                    if ($('#esns_box_content input[name="template_name"]').val().length < 3) {
                        return false;
                    };

                    if ($('#esns_box_content input[name="template_subject"]').val().length < 3) {

                        return false;
                    };
                    return true;
                };

                var updateButton = function () {
                    if (validate()) {
                        $('#esns_box_content .btn-success').removeAttr('disabled');
                    } else {
                        $('#esns_box_content .btn-success').attr('disabled', 'disabled');
                    }
                }

                $('#esns_box_content input[name="template_name"], input[name="template_subject"]').keyup(function(){
                    updateButton();
                });

                $('#esns_box_content select[name="email_template"]').change(function(){
                    var elem = $('#esns_box_content select[name="email_template"]');
                    if (elem.val() != '') {
                        $('#'+fileUploadUid+' .mteditor_upload_button').text(buttonText);
                        $('#esns_box_content input[name="template_ready"]').val(1);
                    } else {
                        $('#esns_box_content input[name="template_ready"]').val(0);
                    }
                    updateButton();
                });

                $('#'+fileUploadUid).fileupload({
                    singleFileUploads: true,
                    url: config.action.templateUploadUrl+'?isAjax=1',
                    formData: {
                        form_key: config.formKey
                    },
                    dropZone: false
                }).bind('fileuploadchange', function (e, data) {
                    $('#esns_box_content .response-error').text('');
                    $('#'+fileUploadUid+' .mteditor_upload_button').text('Uploading....');
                    $('#'+fileUploadUid+' input[type="file"]').attr('disabled', 'disabled');
                    $('#esns_box_content select[name="email_template"]').val('');
                    $('#esns_box_content input[name="template_ready"]').val(0);
                    updateButton();
                }).bind('fileuploaddone', function (e, data) {
                    $('#'+fileUploadUid+' .mteditor_upload_button').text(buttonText);
                    $('#'+fileUploadUid+' input[type="file"]').removeAttr('disabled');
                    var result = data.result;
                    if (result.error) {
                        $('#esns_box_content .response-error').text(result.error);
                        $('#esns_box_content input[name="template_ready"]').val(0);
                    } else if (result.success == 1) {
                        $('#esns_box_content input[name="template_ready"]').val(1);
                        $('#'+fileUploadUid+' .mteditor_upload_button').text('Template File: '+result.file.name);
                    }
                    updateButton();
                });
            }
        });

        $('#esns_box_layer button[data-action="0"]').click(function(){
            window.location = config.action.back;
        });

        $('#esns_box_layer button[data-action="1"]').click(function(){
            sendRequest(config.action.createTemplateUrl, {
                    template_name: $('#esns_box_layer input[name="template_name"]').val(),
                    template_subject: $('#esns_box_layer input[name="template_subject"]').val(),
                    template_id: $('#esns_box_layer select[name="email_template"]').val(),
                }, function(response) {
                    if (response.success && response.success == 1) {
                        window.location = response.redirectTo;
                    } else if (response.error) {
                        $('#esns_box_layer .response-error').html(response.error);
                    }
                }
            );
        });
    };

    var reloadDynamicContentEvent = function() {
        var frameContents = previewFrame.contents();

        frameContents.find('html').unbind('mousedown').mousedown(function (e) {
            enterToPreviewMode();
            return true;
        });

        frameContents.find( "a" ).unbind('click').click(function( event ) {
            event.preventDefault();
        });

        var sortableElement = frameContents.find('#sortable_content');

        frameContents.find('table[data-block]').unbind('click').on('click', function(e) {
            frameContents.find('.active-block').removeClass('active-block');
            $(this).addClass('active-block');

            if (frameContents.find('table[data-block]').length < 2) {
                frameContents.find('.block-action-move').parent().hide();
            } else {
                frameContents.find('.block-action-move').parent().show();
            }


            var pos = $(this).find('*[data-block-content]').offset();
            frameContents.find('.block-action').hide();
            var element = frameContents.find('.active-block .block-action');

            var contentPos = element.parent().find('[data-repeatable]').offset();
            var elmPos = element.offset();
            contentPos.left = contentPos.left - element.width() - 5;
            if (contentPos.top < 10) {
                contentPos.top = 10;
            }

            element.show().offset(contentPos);

            if (element.offset().left < 0) {
                element.css('left', 0);
            }

        }).unbind('mouseover').mouseover(function(e){
            var target = $( e.target );
            if (target.parents(".block-action").length == 0
                && !target.hasClass("block-action")
            ) {
                sortableElement.sortable('disable');
            }
        });

        frameContents.find('.block-action-move').unbind('mouseover').mouseover(function(){
            sortableElement.sortable('enable');
        }).mouseleave(function(){
            sortableElement.sortable('disable');
        });

        frameContents.find('.block-action-delete').unbind('mouseover').mouseover(function(){
            sortableElement.sortable('disable');
        });

        $('#draggable').unbind('mouseover').mouseover(function(){
            if (sortableElement && sortableElement.hasClass('ui-sortable') ) {
                sortableElement.sortable('enable');
            }

        });

        frameContents.find('.block-action-delete').unbind('click').click(function(){
            popup.confirm({
                'msg': 'Are you sure you want to delete this block?',
                'disableAutoClose': true
            }, function(){
                removeActiveBlock();
                popup.close();
            }, function(){
                popup.close();
            });
        });

        frameContents.find('.block-action-source').unbind('click').click(function(e){
            var elm = frameContents.find('.active-block');
            if (elm.length == 0) {
                return;
            }

            sourceEditor.show();
            sourceEditor.updateEditorScroolPosition(e);
        });
    };

    var initStyle = function() {

        initStyleColor('data-group-background-color', 'mtedit_bgcolor', 'background-color', false);
        initStyleColor('data-group-color', 'mtedit_color', 'color', false);

        initStyleColor('data-group-background-color', 'mtedit_body_bgcolor', 'background-color', true);
        initStyleColor('data-group-color', 'mtedit_body_color', 'color', true);
        initColorPicker();

        if (!$('.tools-container input.color').length) {
            $('.empty-style-panel').show();
        } else {
            $('.empty-style-panel').hide();
        }
    };

    var initStyleColor = function(attributeCode, listId, cssAttribute, global) {
        var frameContents = previewFrame.contents();
        var ignoreClass = {
            'mteditor-content-helper-text' : '1',
            'mteditor-content-helper-link' : '1',
            'mteditor-content-helper-img' : '1',
            'mteditor-content-helper-selected' : '1',
            'editor-helper-active' : '1',
            'editor-selected-link' : '1'
        };

        $('#'+listId+' ul').html('');
        var counter = 0;
        var addedClass = {};

        var contextElement = global?frameContents.find('['+attributeCode+']'):frameContents.find('.active-block ['+attributeCode+']');
        contextElement.each(function () {
            var elm = $(this);

            if (global) {
                if(elm.parents('#sortable_content').length == 1) {
                    return;
                }
            }

            var rgbColor = elm.css(cssAttribute);
            var color = toHex(rgbColor);
            var inputTextColor = '#000000';
            if (colorPicker.isDarkColor(rgbColor)) {
                inputTextColor = '#ffffff';
            }
            var value = toHex(elm.css(cssAttribute)).toLowerCase();
            var group = elm.attr(attributeCode);
            var groupHash = hashCode(group);
            if (value.length > 0 && !addedClass[groupHash]) {
                addedClass[groupHash] = 1;
                $('#'+listId+' ul').append('<li><span>'+group+'</span> <input class="color" name="'+group+'" value="'+value+'" style="background-color: '+color+'; color: '+inputTextColor+';"></li>');
                counter++;
            }
        });

        if (counter == 0) {
            $('#'+listId).hide();
            return;
        }
        $('#'+listId).show();

        $('#'+listId+' input').on('change', function(){
            var elementSelector = '['+attributeCode+'="'+$(this).attr('name')+'"]';
            var color = $(this).val();

            if (canApplyToAll() || global) {
                frameContents.find(elementSelector).css(cssAttribute, color);
                if (listId == 'mtedit_bgcolor' || listId=='mtedit_body_bgcolor') {
                    frameContents.find('table'+elementSelector+', table tr'+elementSelector+', table tr td'+elementSelector)
                        .attr('bgcolor', color);
                }
            } else {
                frameContents.find('.active-block '+elementSelector).css(cssAttribute, color);
                if (listId == 'mtedit_bgcolor') {
                    frameContents.find('.active-block table'+elementSelector+', .active-block table tr'+elementSelector+', .active-block table tr td'+elementSelector)
                        .attr('bgcolor', color);
                }
            }
        });
    };

    var loadImageList = function() {
        $.each(config.imageList, function(key, value){
            $('.mteditor-image-list').prepend('<li><img src="'+value+'"/></li>');
        });
    };

    var initImage = function() {
        log('init image');
        var frameContents = previewFrame.contents();
        var activeImg = frameContents.find('.'+textEditHelper.config.classes.helperImg);
        var placeToInsertImage = frameContents.find('.'+textEditHelper.config.classes.helperContentImage);

        var emptyPanel = $('.empty-image-panel');
        if (!activeImg.length && !placeToInsertImage.length) {
            emptyPanel.show();
            return;
        }

        if (!activeImg.length) {
            $('#image_properties').hide();
        } else {
            $('#image_properties').show();
        }

        $('.mteditor_upload_new').show();
        emptyPanel.hide();


        var options = {
            width: activeImg.css('width'),
            height: activeImg.css('height'),
            'margin-top': activeImg.css('margin-top'),
            'margin-right': activeImg.css('margin-right'),
            'margin-bottom': activeImg.css('margin-bottom'),
            'margin-left': activeImg.css('margin-left'),
            style: activeImg.attr('style'),
            alt: activeImg.attr('alt'),
            title: activeImg.attr('title'),

        };

        if (activeImg.attr('width')) {
            options['width'] = activeImg.attr('width');
        }

        if (activeImg.attr('height')) {
            options['height'] = activeImg.attr('height');
        }

        var imgStyle = activeImg.prop('style');
        if (imgStyle && imgStyle.getPropertyValue('width')) {
            options['width'] = imgStyle.width;
        }

        if (imgStyle && imgStyle.getPropertyValue('height')) {
            options['height'] = imgStyle.height;
        }

        $('#image_properties input').each(function () {
            var key = $(this).attr('name').replace('image-', '');
            if (!options[key]) {
                $(this).val('');
                return;
            }
            var value = options[key];
            var mField = $('#image-'+key+'-m');

            if (mField.length) {
                if (value.indexOf('px') != -1) {
                    mField.text('px');
                    value = value.replace('px', '');
                }

                if (value.indexOf('%') != -1) {
                    mField.text('%');
                    value = value.replace('%', '');
                }
            }

            $(this).val(value);
        });

        initImageEvent();
    };

    var initImageEvent = function() {
        var frameContents = previewFrame.contents();
        $('.mteditor-image-list li').unbind('click').click(function() {
            log('click on image');
            var selectedImage = frameContents.find('.'+textEditHelper.config.classes.helperImg);
            var contentEditable = frameContents.find('.'+textEditHelper.config.classes.helperText);

            if (selectedImage.length == 0 && contentEditable.length == 0) {
                return;
            }

            var image = null;
            if (selectedImage.length > 0) {
                image = selectedImage;
            } else {
                image = $('<img />');
            }

            image.attr('src', $(this).find('img').attr('src'));

            if (selectedImage.length > 0) {
                selectedImage.replaceWith(image);
                image.trigger('click');
            } else if (contentEditable.length > 0) {
                contentEditable.append(image);
                image.trigger('click');
            }
        });

        $('#image_properties input').unbind('keyup').keyup(function(){
            if ($(this).attr('name') != 'image-style') {
                updateSelectedImageSize();
                $('input[name="image-style"]').val(frameContents.find('.'+textEditHelper.config.classes.helperImg).attr('style'));
            } else {
                frameContents.find('.'+textEditHelper.config.classes.helperImg).attr('style', $('input[name="image-style"]').val());
            }
        });
    };

    var updateSelectedImageSize = function() {
        var frameContents = previewFrame.contents();
        var imageWidth = $('input[name="image-width"]').val()+ $('#image-width-m').text();
        var imageHeight = $('input[name="image-height"]').val()+ $('#image-height-m').text();

        var cssOptions = {
            'width': imageWidth,
            'height': imageHeight,
            'max-width': imageWidth,
            'margin-top': $('input[name="image-margin-top"]').val() + $('#image-margin-top-m').text(),
            'margin-right': $('input[name="image-margin-right"]').val() + $('#image-margin-right-m').text(),
            'margin-bottom': $('input[name="image-margin-bottom"]').val() + $('#image-margin-bottom-m').text(),
            'margin-left': $('input[name="image-margin-left"]').val() + $('#image-margin-left-m').text(),
        };

        frameContents.find('.'+textEditHelper.config.classes.helperImg)
            .css(cssOptions)
            .attr('alt', $('input[name="image-alt"]').val())
            .attr('title', $('input[name="image-title"]').val())
            .attr('width', imageWidth.replace("px", ""))
            .attr('height', imageHeight.replace("px", ""));


    };

    var initLayout = function() {

        reloadSizes();
        $('#main-menu').metisMenu();
    };

    var hideLayoutMenu = function () {
        var layoutMenu = $('#main-menu').find('[data-selector="edit-layout"]').parent();
        layoutMenu.next().addClass('first');
        layoutMenu.remove();
        openStyleTools();
    };

    var reloadSizes = function() {
        var windowHeight = $(window).height();
        if (config.minWindowHeight > windowHeight) {
            windowHeight = config.minWindowHeight;
        }

        $('#editor_wrapper').height(windowHeight+'px');
        $('.sidebar').height(windowHeight+'px');
        $('#page-wrapper').height(windowHeight+'px');
        $('#email_body').css('max-width', config.templateMaxWidth+'px');
        $('.tools').height(windowHeight+'px');
    };

    var initDragAndDrop = function() {

        var currentMousePos = { x: -1, y: -1 };
        var currentIframeMousePos = { x: -1, y: -1 };
        $(document).mousemove(function(event) {
            currentMousePos.x = event.pageX;
            currentMousePos.y = event.pageY;
        });

        previewFrame.contents().mousemove(function(event) {
            currentIframeMousePos.x = event.pageX;
            currentIframeMousePos.y = event.pageY;
        });

        var sortableHelper;
        var overlay = $('#frame-overlay-');
        var helper = $('#draggable-helper-visible');
        var cursorOffset = {top: 0, left: 0};
        var inIframe = false;
        var previewFrameBottom = previewFrame.height();
        var scrollWraper = previewFrame.contents().find('.scroll-wrapper');

        $("#draggable table").draggable({
            connectToSortable: previewFrame.contents().find('#sortable_content'),
            revert: false,
            zIndex: 22,
            iframeFix: true,
            cursor: "move",
            revertDuration: 0,
            start: function(e, ui) {
                cursorOffset.top = cursorOffset.top - currentMousePos.y;
                cursorOffset.left = cursorOffset.left - currentMousePos.x;
            },

            stop: function(e, ui) {
                $('#draggable-helper-visible').hide();
            },

            drag: function(e, ui) {

                ui.position.left = (currentMousePos.x  - 110);
                ui.position.top = (currentMousePos.y  - 20);

                if (inIframe) {
                    ui.position.left = ui.position.left - previewFrame.offset().left;
                }

                helper.css({
                    top: (currentMousePos.y - 20) + 'px',
                    left: (currentMousePos.x - 110 ) + 'px'
                });

                var helperBottomLine = parseInt(helper.css('top').replace('px', ''))+ helper.height();
                var helperTopLine = parseInt(helper.css('top').replace('px', ''));
                if ((helperBottomLine - 50 ) > previewFrameBottom) {
                    scrollDownStart(scrollWraper);
                } else {
                    scrollDownStop(scrollWraper);
                }

                if (helperTopLine < -50) {
                    scrollUpStart(scrollWraper);
                } else {
                    scrollUpStop();
                }
            },

            helper: function() {
                cursorOffset.top = $(this).offset().top;
                cursorOffset.left =  $(this).offset().left;

                var imgElem = $(this).find('img').clone();
                var elm = $('<div id="draggable-helper-hidden"></div>').append(imgElem.clone().hide());

                $('#draggable-helper-visible').show();
                return elm;
            }

        });

        previewFrame.contents().find('#sortable_content').sortable({
            revert:  false,
            items: '> table[data-block]',
            zIndex: 50,
            cursor: "move",
            iframeFix: true,

            cursorAt: { left: 110, top: 20 },
            helper: function () {
                sortableHelper = $('<div id="draggable-helper-visible" style="display: block; width: 130px; height: 90px"><span></span></div>');
                return sortableHelper;
            },

            placeholder: {
                element: function(currentItem) {
                    if (previewFrame.contents().find('table[data-block]').length == 0) {
                        var placeholder = $('#first_item_placeholder').clone().html();
                        return $(placeholder)[0];
                    }
                    
                    return $('<div class="ui-sortable-placeholder temp-placeholder">' + '<span>Drop Here</span></div>')[0];
                },
                update: function(container, p) {
                    return;
                }
            },

            start: function (event, ui) {
                textEditHelper.hide();
            },

            sort: function (event, ui) {
                if (!sortableHelper || sortableHelper.length == 0) {
                    return;
                }

                var helperBottomLine = sortableHelper.offset().top + sortableHelper.height();
                var helperTopLine = sortableHelper.offset().top;

                if (helperTopLine < -5) {
                    scrollDownStop();
                    scrollUpStart(scrollWraper);
                } else {
                    scrollUpStop();
                    if ((helperBottomLine - 5 ) > previewFrameBottom) {
                        scrollDownStart(scrollWraper);
                    } else {
                        scrollDownStop();
                    }
                }
            },

            beforeStop: function (event, ui) {
                scrollUpStop();
                scrollDownStop();

                if (!ui.item.data('id')) {
                    return;
                }
                ui.item.replaceWith($('#block_placeholder').html());
            },

            stop: function (event, ui) {
                previewFrame.contents().find('.empty-placeholder').remove();
            },

            over: function(event, ui) {
                inIframe = true;
                previewFrame.contents().find('.empty-placeholder').remove();
            },

            out: function() {
                inIframe = false;
                initPlaceholder();
            },

            update: function(event, ui) {
                var blockId = ui.item.find('img').data('id');
                if (!blockId) {
                    return;
                }

                var newBlock = config.data[blockId]['content'];
                var item = $(contentHelper.decorateBlock(newBlock, config));
                previewFrame.contents().find('#draggable-helper-hidden').replaceWith(item.hide().fadeIn({duration: 1000}));
                reloadDynamicContentEvent();
            },

        });
    };

    var scrollDownStart = function (elm) {
        if (!scroll.down) {
            scroll.down = true;
            scrollDown(elm);
        }
    };

    var  scrollDownStop = function()
    {
        scroll.down = false;
    }

    var scrollDown = function (elm) {
        if (!scroll.down) {
            return;
        }
        setTimeout(function () {
            elm.scrollTop(elm.scrollTop()+4);
            scrollDown(elm);
        }, 5);
    }

    var scrollUpStart = function (elm) {
        if (!scroll.up) {
            scroll.up = true;
            scrollUp(elm);
        }
    };

    var  scrollUpStop = function()
    {
        scroll.up = false;
    };

    var scrollUp = function (elm) {
        if (!scroll.up) {
            return;
        }
        setTimeout(function () {
            elm.scrollTop(elm.scrollTop()-4);
            scrollUp(elm);
        }, 5);
    };

    var initEditorEvent = function() {
        $(document).mousedown(function (e) {
            var container = $("#source_panel");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                enterToPreviewMode();
            } else {
                enterToSourceEditMode();
            }
            return true;
        });

        $( "a" ).click(function( event ) {
            event.preventDefault();
        });

        $(window).resize(function(){
            reloadSizes();
        });

        $('a[data-selector="edit-layout"]').unbind('click').click(function(){
            openLayoutTools();
        });

        $('a[data-selector="edit-style"]').unbind('click').click(function(){
            openStyleTools();
        });

        $('a[data-selector="edit-image"]').unbind('click').click(function(){
            openImageTools();
        });


        $('.nav li a').click(function(){
            $('.nav li a').removeClass('active');
            $(this).addClass('active');
        });

        $('#email .block').click(function(){
            $('a.open-tools[data-selector="edit-layout"]').trigger('click');
        });

        $('#switch').click(function() {
            if ($(this).hasClass('inactive')) {
                $('#switch_thumb').switchClass("inactive", "active", 100, "linear");
                $('#switch').switchClass("inactive", "active", 100, "linear");
            } else {
                $('#switch_thumb').switchClass("active", "inactive", 100, "linear");
                $('#switch').switchClass("active", "inactive", 100, "linear");
            }
        });


        $('#switch_apply_to_all').click(function() {
            if ($(this).hasClass('inactive')) {
                $('#switch_thumb_apply_to_all').switchClass("inactive", "active", 100, "linear");
                $('#switch_apply_to_all').switchClass("inactive", "active", 100, "linear");

            } else {
                $('#switch_thumb_apply_to_all').switchClass("active", "inactive", 100, "linear");
                $('#switch_apply_to_all').switchClass("active", "inactive", 100, "linear");

            }
        });



        $('button[data-action="back"]').click(function(){
            popup.confirm({
                'msg': 'Do you want to save the changes?',
                'disableAutoClose': true
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Saving...');
                saveHelper.save(function(response){
                    window.location = config.action.back;
                });
            }, function(){
                window.location = config.action.back;
            });
        });

        $('a[data-action="preview-full-screen"]').click(function(){
            var tab = window.open('about:blank', '_blank');
            preparePreviw($(tab.document));
            tab.document.close();
        });

        $('a[data-action="preview-mobile"]').click(function(){
            popup.initPopup({
                contentSelector: '#mobile_preview',
                disableClose: false,
                beforeResize: function () {

                },

                beforeOpen: function () {
                    preparePreviw($('#esns_box_layer iframe').contents());
                },
            });
        });

        $('a[data-action="preview-tablet"]').click(function(){
            popup.initPopup({
                contentSelector: '#tablet_preview',
                disableClose: false,
                beforeResize: function () {
                },

                beforeOpen: function () {
                    preparePreviw($('#esns_box_layer iframe').contents());
                },
            });
        });


        $('a[data-action="send-email"]').click(function(){
            var previewUrl = '';

            popup.content({
                contentSelector: '#send_test_message',
                disableClose: false
            }, function(){

                $('input[name="send_email[email]"]').unbind('keyup').keyup(function(){
                    var email = $(this).val();
                    var actionButton = $('#esns_box_content button[data-action="1"]');
                    if (!isEmailValid(email)) {
                        actionButton.attr('disabled', 'disabled');
                        return;
                    }
                    actionButton.removeAttr('disabled');
                });

                var lastEmail = $.cookie('last_test_email');
                if (lastEmail && isEmailValid(lastEmail)) {
                    $('#esns_box_content input[name="send_email[email]"]').val(lastEmail);
                    $('#esns_box_content button[data-action="1"]').removeAttr('disabled');
                }
                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

                $('button[data-action="1"]').unbind('click').click(function(){
                    var email = $('#esns_box_content input[name="send_email[email]"]').val();
                    if (isEmailValid(email)) {
                        $.cookie('last_test_email', email, { expires: 199, path: '/' });
                        var button = $(this);
                        button.text('Sending...');
                        $('#esns_box_content .response-error').hide();
                        $('#esns_box_content .response-success').hide();

                        sendRequest(config.action.sendTestEmilUrl, {
                            source: JSON.stringify(sourceEditor.getHtml()),
                            id: config.template_id,
                            email: email
                        }, function(response) {
                            if (response.success == 1) {
                                $('#esns_box_content .response-success').text('Email has been sent successful.').show();

                            } else {
                                $('#esns_box_content .response-error').text(response.error).show();
                            }
                            button.text('Send');
                        });
                    }
                });

                $('#esns_box_content a[data-action="0"]').click(function(e){
                    popup.close(true);
                });
            }, function() {
            }, function() {

            });

        });

        $('a[data-action="change-info"]').click(function(){
            popup.content({
                contentSelector: '#change_info',
                disableClose: false,
                disableCloseAfterSubmit: true
            }, function(){
                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();
                $('#esns_box_content input[name="template_code"]').val(config.template.code);
                $('#esns_box_content input[name="template_subject"]').val(config.template.subject);
                $('#esns_box_content input[name="template_sender_name"]').val(config.template.sender_name);
                $('#esns_box_content input[name="template_sender_email"]').val(config.template.sender_email);
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Saving...');
                var newTemplateCode = $('#esns_box_content input[name="template_code"]').val();
                var newTemplateSubject = $('#esns_box_content input[name="template_subject"]').val();
                var newTemplateSenderName = $('#esns_box_content input[name="template_sender_name"]').val();
                var newTemplateSenderEmail = $('#esns_box_content input[name="template_sender_email"]').val();
                sendRequest(config.action.saveInfo, {
                        template_code: newTemplateCode,
                        template_subject:  newTemplateSubject,
                        template_sender_email:  newTemplateSenderEmail,
                        template_sender_name:  newTemplateSenderName,
                        template_id: config.template_id
                    }, function(response) {
                        if (response.success == 1) {
                            config.template.code = newTemplateCode;
                            config.template.subject = newTemplateSubject;
                            config.template.sender_name = newTemplateSenderName;
                            config.template.sender_email = newTemplateSenderEmail;
                            $('#esns_box_content .response-error').hide();
                            $('#esns_box_content .response-success').text('Template has been saved successful!').show();
                            $('#esns_box_layer a[data-action="1"]').text('Save');
                            setTimeout(function(){
                                popup.config.disableClose = false;
                                popup.close(true);
                            }, 2000);
                        } else if (response.error.length > 0) {
                            $('#esns_box_content .response-error').text(response.error).show();
                            $('#esns_box_content .response-success').hide();
                        }
                    }
                );
            }, function(){
                popup.close();
            });
        });

        $('a[data-action="delete-template"]').click(function(){
            popup.confirm({
                'msg': 'Are you sure? Do You want to delete this template?',
                'disableAutoClose': true
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Deleting...');
                sendRequest(config.action.deleteTemplateAjax, {
                        template_id: config.template_id
                    }, function(response) {
                        if (response.success == 1) {
                            window.location = config.action.back;
                        } else if (response.error.length > 0) {

                        }
                    }
                );
            }, function(){
                popup.close(true);
            });
        });


        $('a[data-selector="edit-css"]').unbind('click').click(function(){
            var cssElement = $('style[data-helper="css"]');
            var editor;
            popup.initPopup({
                contentSelector: '#edit_css',
                disableClose: false,
                beforeResize: function () {
                    $('#esns_box_layer textarea[name="css"]').css({
                        width: ($(window).width() - 150) + 'px',
                        height: ($(window).height() - 255) + 'px'
                    });

                    var textAreaElement = $('#esns_box_layer textarea[name="css"]');
                    editor.setSize(textAreaElement.width(), textAreaElement.height());
                    editor.refresh();
                },

                beforeOpen: function () {
                    var textAreaElement = $('#esns_box_layer textarea[name="css"]');

                    textAreaElement.val(removeEncapsulationCss(cssElement.text(), 'newsletter_maker_content'));
                    textAreaElement.attr('id', 'css_source_edit');

                    editor = CodeMirror.fromTextArea(document.getElementById("css_source_edit"), {
                        mode: 'css',
                        lineNumbers: true,
                        selectionPointer: false,
                        gutters: ["CodeMirror-linenumbers"]
                    });
                },

                afterOpen: function () {
                    editor.refresh();
                },

                success: function () {
                    cssElement.text(encapsulateCss(editor.getValue(), {prefix: 'newsletter_maker_content'}));
                    popup.close(true);
                },

                cancel: function () {
                    popup.close(true);
                }
            });
        });

        $('a[data-selector="edit-source"]').unbind('click').click(function(e){
            sourceEditor.toggle();
            sourceEditor.updateEditorScroolPosition(e);
        });
    };



    var openLayoutTools = function() {
        beforeOpenLayoutTools();
        openTools('edit-layout');
    };

    var openImageTools = function() {
        beforeOpenImageTools();
        openTools('edit-image');
    };

    var openStyleTools = function() {
        beforeOpenStyleTools();
        openTools('edit-style');
    };


    var openTools = function(className) {
        var openPanel = '.tools.' + className;
        if ($(openPanel).hasClass('active')) {
            return false;
        }
        $('.nav a[data-selector]').removeClass('active');
        $('.nav a[data-selector="'+className+'"]').addClass('active');
        $( '.tools').css('z-index', 3);
        $(openPanel).css('z-index', 4);
        $( '.tools.active' ).animate({
            left: '-108'
        }, 200, function() {
            $(openPanel).animate({
                left: '200'
            }, 200).addClass('active');
        }).removeClass('active');
    };

    var updateSource = function(htmlSource)
    {
        htmlSource = htmlSource.replace('<body', '<div data-id="body"');
        htmlSource = htmlSource.replace('</body>', '</div>');
        $('#email').html(htmlSource);
        reloadContent();
        initDragAndDrop();
    }

    var isEmailValid = function(value) {
        if ( value.length >= 6 && value.split('.').length > 1 && value.split('@').length == 2) {
            return true;
        }
        return false;
    };

    var initFileUpload = function() {
        $('#imageupload').fileupload({
            singleFileUploads: true,
            url: config.action.uploadUrl+'?isAjax=1',
            formData: {
                form_key: config.formKey,
                id: config.template_id
            },
            dropZone: undefined
        }).bind('fileuploadchange', function (e, data) {
            $('#imageupload .mteditor_upload_button').text('Uploading....');
            $('#imageupload input[type="file"]').attr('disabled', 'disabled');
            $('#imageupload .fileupload-buttonbar i.glyphicon').removeClass('glyphicon-plus').addClass('glyphicon-upload');
        }).bind('fileuploaddone', function (e, data) {
            $('#imageupload .mteditor_upload_button').text('Select image');
            $('#imageupload input[type="file"]').removeAttr('disabled');
            $('#imageupload .fileupload-buttonbar i.glyphicon').addClass('glyphicon-plus').removeClass('glyphicon-upload');

            var result = data.result;
            if (result.success == 1) {
                $('.mteditor-image-list').prepend('<li><img src="'+result.fileUrl+'"/></li>');
                initImage();
            }
        });
    };

    var removeActiveBlock = function() {
        previewFrame.contents().find('.active-block').remove();
        initStyle();
        initPlaceholder();
    };

    var initPlaceholder = function() {
        var containerElement = previewFrame.contents().find('#sortable_content');
        if (containerElement.find('table[data-block]').length == 0) {
            containerElement.append($('#empty_placeholder').clone().html());
            containerElement.sortable('enable');
            $('a[data-selector="edit-layout"]').trigger('click');
        }
    };

    var canApplyToAll = function() {
        return $('#switch').hasClass('active');
    };

    var initColorPicker = function() {
        colorPicker.init();
    };

    var toHex = function(color) {
        if (!color) {
            return '#ffffff';
        }

        if (color == 'rgba(0, 0, 0, 0)' || color == 'transparent') {
            return '';
        }

        if (color.substr(0, 1) === '#') {
            return color;
        }
        var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
        if (digits == null) {
            var digits = /(.*?)rgba\((\d+), (\d+), (\d+), (\d*(?:\.\d+)?)\)/.exec(color);
        }

        if (digits == null) {
            return '';
        }

        var red = parseInt(digits[2]);
        var green = parseInt(digits[3]);
        var blue = parseInt(digits[4]);

        return digits[1] + '#' + componentToHex(red) + '' + componentToHex(green) + '' + componentToHex(blue) ;
    };

    var componentToHex = function(c) {
        var hex = c.toString(16);
        return hex.length == 1 ? "0" + hex : hex;
    }

    var initPopup = function(){
        popup.init();
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

    var beforeOpenImageTools = function() {
        log('beforeOpen Image Tools');
        initImage();
    };

    var beforeOpenLayoutTools = function() {
        log('beforeOpen Layout Tools');
        $('#sortable_content').sortable('enable');
    };

    var beforeOpenStyleTools = function() {
        log('beforeOpen Style Tools');
        initStyle();
    };

    var log = function(msg) {
        if (config.log == 1) {
            console.log(msg);
        }
    };

    var preloadImages = function() {
        $('.mteditor-image-list img').each(function(){
            $("<img />").attr("src", $(this).attr('src'));
        })
    };

    var showLoading = function() {
        popup.content({
            contentSelector: '#loading',
            disableClose: true,
            disableCloseAfterSubmit: true
        }, function(){}, function(){});
    };

    var hideLoading = function()
    {
        popup.close(true);
    };

    var getBlockUid = function () {
        var maxId = 0;
        $('table[data-block]').each(function () {
            if (maxId < $(this).data('block')) {
                maxId = $(this).data('block');
            }
        });
        return maxId + 1;
    };

    var preparePreviw = function (doc) {
        var previewFrameContents = previewFrame.contents();
        doc.find('body').html(contentHelper.cleanContent(previewFrameContents.find('body').html()));
        doc.find('head').html(contentHelper.cleanContent(previewFrameContents.find('head').html()));
        return doc;
    };

    var isSingleBlockMode = function () {
        return config.data.length == 0;
    };

    var toHtml = function (element) {
        return $('<div></div>').append(element).html();
    };

    var hashCode = function(s) {
        return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);
    };

    var loadMissingFonts = function () {
        var options = {};
        $('select[name="font-family"] option').each(function () {
            options[$(this).text()] = 1;
        });

        previewFrame.contents().find('[contenteditable]').each(function () {
            var font = $(this).css('font-family').split('"').join('').split("'").join('');
            if (!options[font]) {
                var fontValue = $(this).css('font-family').split('"').join('\"').split("'").join("\'");
                $('select[name="font-family"]').append($('<option value="'+font+'">'+font+'</option>'));
                options[font] = 1;
            }
        });
    };

    var saveBodyTop = function () {

        if (previewFrame.contents().scrollTop() > previewFrame.contents().find('.scroll-wrapper').scrollTop()) {
            previewFrameTop = previewFrame.contents().scrollTop()
        } else {
            previewFrameTop = previewFrame.contents().find('.scroll-wrapper').scrollTop();
        }
    };

    var restoreBodyTop = function () {
        if (previewFrameTop == 0) {
            return;
        }

        setTimeout(function () {
            previewFrame.contents().find('.scroll-wrapper').scrollTop(previewFrameTop).attr('data-top', previewFrameTop);
            previewFrame.contents().scrollTop(previewFrameTop);
        }, 0);
    };

    return {
        init: init,
        config: config,
        log: log,
        reloadDynamicContentEvent: reloadDynamicContentEvent,
        reloadPreviewFrameEvents: reloadPreviewFrameEvents,
        initImage: initImage,
        openStyleTools: openStyleTools,
        openImageTools: openImageTools,
        isSingleBlockMode: isSingleBlockMode,
        toHex: toHex,
        toHtml: toHtml,
        saveBodyTop: saveBodyTop,
        restoreBodyTop: restoreBodyTop
    };

})(jQuery);
