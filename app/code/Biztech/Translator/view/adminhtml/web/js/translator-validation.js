require([
    'jquery',
    'mage/translate',
    'jquery/validate'],
    function($){

        $.validator.addMethod(
            "validate-daily-quota", 
            function (v , element) {
                $(element).val($.trim(v));
                if(parseInt($(element).val()) > parseInt($('#translator_general_google_daily_cut_before_limit').val())){
                    return true;
                }else{
                    return false;
                }
		     },
		    $.mage.__("Daily Quota limits can not be less than the Safe Limit.")
		);
        $.validator.addMethod(
            "validate-safe-limit", 
            function (v , element) {
                $(element).val($.trim(v));
                if(parseInt($(element).val()) > parseInt($('#translator_general_google_daily_limit').val())){
                    return false;
                }else{
                    return true;
                }
             },
            $.mage.__("Safe Limit can not be more than the Daily Quota limits.")
        );
        $.validator.addMethod(
            "multiple-email-validate", 
            function (v , element) {
                $(element).val($.trim(v));
                var mails = $('#translator_general_cron_mail_id').val();
                var result = mails.replace(/\s/g, "").split(/,|;/);
                var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                var validate = true;
                for(var i = 0;i < result.length;i++) {
                    if(!regex.test(result[i])) {
                        validate = false;
                    }
                }
                if(validate==true) {
                    return true;
                } else {
                    return false;
                }
             },
            $.mage.__("Please enter valid mail.")
        );
    }
);
