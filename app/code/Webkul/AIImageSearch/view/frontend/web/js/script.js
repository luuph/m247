/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIImageSearch
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'mage/url',
    'mage/translate',
    'mage/template',
    "cropper_js",
    'Magento_Ui/js/modal/alert',
    "Magento_Ui/js/modal/modal"
], function ($, url, $t, mageTemplate, cropper, alert) {
    "use strict";
    $.widget("image.search", {
        options: {},
        _create: function () {
            let self = this;
            let imagename = '';
            let cropper = '';
            let cropSrc = '';
            let origSrc = '';
            let modalHtml = `
            <main class="wk-ai-search-by-image-page">
                <div class="wk-ai-search-by-image-box-2">
                    <div class="wk-ai-advanced-image-result"></div>
                    <button class="crop_image" class="btn save">${$t('Save Changes')}</button>
                </div>
            </main>`;
            url.setBaseUrl(BASE_URL);
            $(document).ready(function () {
                $("#wk_ai_image_search_icon").on("click", function () {
                    $('#image-input').trigger('click');
                });
                $("#image-input").change(function(e) {
                    if (e.target.files.length) {
                        const reader = new FileReader();
                        reader.onload = (e)=> {
                            if(e.target.result){
                                let previewPopup = $('<div></div>').html(modalHtml);
                                previewPopup.modal({
                                    title: $t('Search By Image'),
                                    innerScroll: true,
                                    modalClass: '_image-box',
                                    buttons: []
                                }).trigger('openModal');
                                imagename = $("#image-input").val().substring(12);  
                                let img = document.createElement('img');
                                img.id = 'image-edit';
                                img.src = e.target.result
                                $("#image-input").val('');
                                $(".wk-ai-advanced-image-result").append(img);
                                cropper = new Cropper(img); 
                                $('.crop_image').on('click', function (e){
                                    e.preventDefault();
                                    let imgSrc = cropper.getCroppedCanvas({
                                        width: 300 // input value
                                    }).toDataURL();
                                    cropSrc = imgSrc;
                                    origSrc = $('#image-edit').attr('src');
                                    $.ajax({
                                        url: url.build('aiimagesearch/session/index'), 
                                        type: "POST",
                                        data: {
                                            origimage: origSrc,
                                            cropimage: cropSrc,
                                            name: imagename
                                        },
                                        success: function(result){
                                            if('image_name' in result){
                                                let link = url.build('catalogsearch/result/?q='+result.image_name);
                                                window.location.href = link;
                                            } else if('error' in result) {
                                                alert({
                                                    title: $t('Error'),
                                                    content: result.error
                                                });
                                            }
                                        }
                                    }); 
                                });
                                $('.action-close').on('click', function (e){
                                    previewPopup.remove();
                                });
                            }
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            });
        },
    });
    return $.image.search;
});
