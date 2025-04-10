require([
    "jquery",
    "Magento_Ui/js/modal/confirm",
    "mage/translate"
    ], function ($, confirm) {
        "use strict";
        function getBannerForm(url)
        {
            return $("<form>", {"action":url, "method":"POST"})
            .append($("<input>", {
                    "name": "form_key",
                    "value": window.FORM_KEY,
                    "type": "hidden"
                }));
        }
        $("#image-edit-delete-button").click(function () {
            var confirmationMsg = $.mage.__("Are you sure you want to do this?");
            var deleteUrl = $("#image-edit-delete-button").data("url");
            confirm({
                "content": confirmationMsg,
                "actions": {
                    confirm: function () {
                        getBannerForm(deleteUrl).appendTo("body").submit();
                    }
                }
            });
            return false;
        });

        $(document).on('keyup','input[name="mobikul_walkthrough[title]"]',function(){
            let walktitle=$(this).val();
            if(walktitle.length>0 && walktitle.length<100){    
                $('.errortitle').remove()
                return true;                
                }
            else{
                $('.errortitle').remove()
                $(this).parent('div').append('<lable class="admin__field-error errortitle" id="error-title">Title can not be more then 50 characters </lable>')
            }    
        });
        $(document).on('keyup','textarea[name="mobikul_walkthrough[description]"]',function(){
            let walkdescription=$(this).val();
            if(walkdescription.length>0 && walkdescription.length<400){    
                $('.errordescription').remove()
                return true;
                }
            else{
                $('.errordescription').remove()
                $(this).parent('div').append('<lable class="admin__field-error errordescription" id="error-description">Description can not be more then 200 characters </lable>')
            }    
        });
    });