define('biztechTranslatorv213',[
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function(jQuery, translate, modal, alert, confirm, events){

    jQuery(document).ready(function() {
        jQuery('#search_translate_form').submit(function(event) {
            event.preventDefault();
        });
    });
    jQuery(document).ready(function() {
        jQuery('#custom_module_search_translate_form').submit(function(event) {
            event.preventDefault();
        });
    });

    Window.keepMultiModalWindow = true;

    var BiztechTranslatorForm = {
        overlayShowEffectOptions : null,
        overlayHideEffectOptions : null,
        modal: null,
        init : function(form, BiztechTranslatorConfig){
            if(typeof BiztechTranslatorConfig === 'string'){
                BiztechTranslatorConfig = JSON.parse(BiztechTranslatorConfig);
            }
            this.translateURL = BiztechTranslatorConfig.url;
            this.fullFromLanguageName = BiztechTranslatorConfig.fullFromLanguageName;
            this.languageToFullName = BiztechTranslatorConfig.languageToFullName;
            this.languageToCode = BiztechTranslatorConfig.languageToCode;
            this.fullFromCode = BiztechTranslatorConfig.fullFromCode;
            this.translateBtnText = BiztechTranslatorConfig.translateBtnText;

            this.popupOverlay = jQuery('#biztech-translator');
            this.errorOverlay = jQuery('#error-overlay');

            if( BiztechTranslatorConfig.translatedFieldsNames ) {
                var translatedFieldsNames = BiztechTranslatorConfig.translatedFieldsNames.split(',');
                jQuery.each(translatedFieldsNames, function(index, val) {
                    //var elId = '#'+val;
                    var elId = jQuery('[data-index="'+val+'"] input[type="text"]').attr("id");
                    if(!elId){
                        elId = jQuery('[data-index="'+val+'"] textarea').attr("id");
                    }
                    if(val == "content"){
                        elId = jQuery('[name="'+val+'"]').attr("id");
                    }
                    
                    elId = "#"+ elId;
                    if (this.languageToCode === 'no-language' || this.languageToCode === 'null' || this.languageToCode === 'undefined') {
                        button = "<span style='padding-right: 10px;'><i>" + jQuery.mage.translate.translate('Select Language for this store in System->Config->Translator') + "</i></span>";
                    } else {
                        button = '<button id="biz_'+ index +'" title="' + jQuery.mage.translate.translate('Translate to ') + this.languageToFullName + '" type="button" class="scalable btn-translate" onclick="BiztechTranslatorForm._submit(\''+ this.translateURL +'\',\''+ elId +'\',\''+ val +'\')" ><span>'+ jQuery.mage.translate.translate(this.translateBtnText) + ' ' + this.languageToFullName + '</span></button>';
                    }
                    jQuery(elId).siblings(".btn-translate").remove();
                    if (jQuery(elId).parents('.admin__control-wysiwig').length) {
                        jQuery(elId).parents('.admin__control-wysiwig').parent().closest('div').siblings(".btn-translate").remove();
                        jQuery(elId).parents('.admin__control-wysiwig').parent().closest('div').after(button);
                    } else {
                        jQuery(elId).after(button);
                    }

                }.bind(this));

            };
        },
        initReview : function(form, BiztechTranslatorConfig){
            if(typeof BiztechTranslatorConfig === 'string'){
                BiztechTranslatorConfig = JSON.parse(BiztechTranslatorConfig);
            }
            this.translateURL = BiztechTranslatorConfig.url;
            this.fullFromLanguageName = BiztechTranslatorConfig.fullFromLanguageName;
            this.languageToFullName = BiztechTranslatorConfig.languageToFullName;
            this.languageToCode = BiztechTranslatorConfig.languageToCode;
            this.fullFromCode = BiztechTranslatorConfig.fullFromCode;
            this.translateBtnText = BiztechTranslatorConfig.translateBtnText;

            this.popupOverlay = jQuery('#biztech-translator');
            this.errorOverlay = jQuery('#error-overlay');

            if( BiztechTranslatorConfig.translatedFieldsNames ) {
                var translatedFieldsNames = BiztechTranslatorConfig.translatedFieldsNames.split(',');

                jQuery.each(translatedFieldsNames, function(index, val) {
                    var elId = '#'+val;
                    if (this.languageToCode === 'no-language' || this.languageToCode === 'null' || this.languageToCode === 'undefined') {
                        button = "<span style='padding-right: 10px;'><i>" + jQuery.mage.translate.translate('Select Language for this store in System->Config->Translator') + "</i></span>";
                    } else {
                        button = '<button id="biz_'+ index +'" title="' + jQuery.mage.translate.translate('Translate to ') + this.languageToFullName + '" type="button" class="scalable btn-translate" onclick="BiztechTranslatorForm._submit(\''+ this.translateURL +'\',\''+ elId +'\',\'' + val +'\')" ><span>'+ jQuery.mage.translate.translate(this.translateBtnText) + ' ' + this.languageToFullName + '</span></button>';
                    }
                    jQuery(elId).siblings(".btn-translate").remove();
                    jQuery(elId).after(button);

                }.bind(this));

            };
        },
        _submit : function(url,el,val){

            var formdata = {
                'langto' : this.languageToCode,
                'langfrom' : this.fullFromCode,
                'id' : el,
                'value': jQuery(el).val(),
                'attributeCode' : val
            };

            jQuery.ajax({
                url : url,
                type: 'POST',
                data : formdata,
                showLoader : true,
                success : this._processResult.bind(this)
            }).fail(function(data){
                result = jQuery.parseJSON(data);
                if( result.value.status === 'fail' ){
                    alert({
                        content : jQuery.mage.translate.translate('Unknown Error!')
                    });
                }
            }.bind(this));
        },
        _processResult : function(transport){
            var response = '',e='';
            try {
                response = jQuery.parseJSON(transport);

                if( response.value.status == 'success' ){
                    this.openDialogWindow(response);
                } else {
                    alert({
                        content : response.value.text
                    });
                }
            } catch (e){
                alert({
                    content : e.message
                });
            }
        },
        openDialogWindow : function (responseData) {
            var self = this;

            if (this.modal) {
                this.modal.html(jQuery(this.popupOverlay).html());
            } else {
                this.modal = jQuery(this.popupOverlay).modal({
                    title : 'AppJetty Translator',
                    modalClass: 'product-translate-popup',
                    type: 'popup',
                    firedElementId: responseData.id,
                    elID : responseData.id,
                    buttons: [{
                        text: jQuery.mage.translate.translate('Apply Translate'),
                        click: function () {
                            self.okDialogWindow(this);
                        }
                    }],
                    closed: function () {
                        self.closeDialogWindow(this);
                    }
                });
            }
            if(responseData.value.status=='success'){
                this.modal.modal('openModal');
                this.modal.find('#translation-lang').html(this.languageToFullName);
                this.modal.find('#original-lang').html(this.fullFromLanguageName);
                this.modal.find('.old-text').val( jQuery(responseData.id).val() );
                this.modal.find('.translated-textarea').val(responseData.value.text);
            }else{
                this.modal.modal('openModal');
                this.modal.content('responseData.value.text');
            }
        },
        closeDialogWindow : function(dialogWindow){
            var Windows='';
            this.modal = null;
            if(jQuery.isFunction(dialogWindow.closeModal)){
                dialogWindow.closeModal();
            }
            
            Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
        },
        okDialogWindow : function(dialogWindow){
            if( dialogWindow.options.firedElementId ){

                //check if textarea is simple or editor
                //if (jQuery(dialogWindow.options.firedElementId).next('span').length) {
                if (jQuery(dialogWindow.options.firedElementId+'_ifr').length) {
                    
                    //var editor = jQuery.mage.translate.tinyMCE.getInstanceById(jQuery(dialogWindow.options.firedElementId).attr('id'));
                    // var editor = jQuery.mage.translate.tinyMCE.get(jQuery(dialogWindow.options.firedElementId).attr('id'));
                    var editor = window.tinyMCE.get(jQuery(dialogWindow.options.firedElementId).attr('id'));
                    if( editor !== null ){
                        editor.execCommand( 'mceSetContent' , true, this.modal.find('.translated-textarea').val() );
                     //   editor.change();
                    } else {
                        jQuery(dialogWindow.options.firedElementId).val(this.modal.find('.translated-textarea').val());
                         jQuery(dialogWindow.options.firedElementId).change();
                    }
                } else {

                     jQuery(dialogWindow.options.firedElementId).val(this.modal.find('.translated-textarea').val());
                      jQuery(dialogWindow.options.firedElementId).change();
                }

            }
            this.closeDialogWindow(dialogWindow);
        },
        matchSearchString : function(url){
            var parameters = jQuery('#search_translate_form').serializeArray();

            jQuery.ajax({
                url: url,
                type: 'POST',
                data: parameters,
                showLoader : true
            }).done(function(data) {
                response = JSON.parse(data);

                var result = jQuery('#search-result').length;

                if( result === 0 ){
                    jQuery('#searchResult').after('<div id="search-result">'+ response.data +'</div>');
                } else {
                    jQuery('#search-result').html(response.data);
                }
            }).fail(function(data){
                response = JSON.parse(data);
                alert({
                    content : response.text
                });
            });
        },
        translateSearchReset : function(){
            jQuery('#search-result').html('');
        },
        custommatchSearchString : function(url){
            var parameters = jQuery('#custom_module_search_translate_form').serializeArray();
            confirm({
                title: "Translate Module Static Data",
                content: "Are you sure to translate module's static data in above filtered <b>Store</b> and <b>Language</b>?",
                actions: {
                    confirm: function () {
                        jQuery.ajax({
                            url: url,
                            type: 'POST',
                            data: parameters,
                            showLoader : true
                        }).done(function(data) {
                            response = JSON.parse(data);

                            var result = jQuery('#custom-module-search-result').length;

                            if( result === 0 ){
                                jQuery('#custommodulesearchResult').after('<div id="custom-module-search-result">'+ response.data +'</div>');
                            } else {
                                jQuery('#custom-module-search-result').html(response.data);
                            }
                        }).fail(function(data){
                            response = JSON.parse(data);
                            alert({
                                content : response.text
                            });
                        });
                    }
                }
            });
        },
        customtranslateSearchReset : function(){
            jQuery('#custom-module-search-result').html('');
        },
        adminTranslation : function(){
            jQuery('#translate_error_msg').html('');
            var url = jQuery('#translate_url').val();

            var formdata = {
                'langto' : jQuery('#locale').val(),
                'langfrom' : '',
                'value' : jQuery('#original_translation').val()
            };

            jQuery.ajax({
                url: url,
                type: 'POST',
                data: formdata,
                showLoader: true
            }).done(function( data ) {
                var response = JSON.parse(data);

                if( response.status === 'fail' ){
                    alert({
                        content : response.text
                    });
                } else {
                    jQuery('#string').val( response.text );
                }
            }).fail(function(data) {
                response = JSON.parse(data);
                alert({
                    content : response.text
                });
            });

        }
    };
    window.BiztechTranslatorForm = BiztechTranslatorForm;
    return {
        BiztechTranslatorForm : BiztechTranslatorForm
    };
});
