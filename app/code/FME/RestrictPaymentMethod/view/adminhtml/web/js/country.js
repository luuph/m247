require([
    'jquery',
    'jquery/ui',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
    ], function($,_, uiRegistry, select, modal){
        $(document).ready(function() {
            $(window).scroll(function(){
                var value = $('div[data-index="restrictoptions"] div select[name="restrictoptions"]').val();
                var field1 = $('div[data-index = "country"]');
                var field2 = $('div[data-index = "region_id"]');
                field2.css("display", "block");
                field1.css("display", "block");
                if (value == 0) {
                    field1.css("display", "block");
                } else {
                    field1.css("display", "none");
                }

                if (value == 1) {
                    field2.css("display", "block");
                } else {
                    field2.css("display", "none");
                }
            });
        }); 
    }); 