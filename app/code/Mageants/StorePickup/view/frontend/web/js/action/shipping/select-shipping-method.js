/**
 * Mageants Store Pickup Magento2 Extension 
 */ 
/*global define,alert*/
define(
    [
        'Magento_Checkout/js/model/quote'
    ],
    function (quote) {
        "use strict";
        return function (shippingMethod) {
            quote.shippingMethod(shippingMethod)
            
            if(shippingMethod != null)
            {
                //alert(JSON.stringify(shippingMethod));
                var code = shippingMethod.carrier_code;
                   jQuery.cookie("selected-val", code, {path: '/'});
                if(code == 'storepickup'){
                    jQuery('#store-pickup-additional-block').show();
                     jQuery('.checkout-comment-block').show();
                      jQuery('.delivery-information').hide();
                      
                   
                }
                else{
                    jQuery('#store-pickup-additional-block').hide();
                    jQuery('.checkout-comment-block').hide();
                     jQuery('.delivery-information').show();
                     
                }
                 
            } 
            jQuery("#pickup_store").change(function(){
                jQuery( "#pickup_date" ).focus();
            });          
        }
    }
);