/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
        'jquery',
    ],
    function ($, orderData) {
        return function (orderData) {
            let walletTotalAmount = orderData.walletTotalAmount;
            let currencySymbol = orderData.currencySymbol;
            let grandTotal = orderData.grandTotal;
            let lefToPayAmount = orderData.lefToPayAmount;
            let leftInWallet = orderData.leftInWallet;

            lefToPayAmount = leftInWallet - grandTotal ;
            leftInWallet = leftInWallet - grandTotal ;

            if ( lefToPayAmount >= 0 ) {
                    lefToPayAmount = 0;
            }

            if (lefToPayAmount < 0) {
                $('.wk_ws_leftamount_th').css("color", "#ef5350");
                $('.wk_ws_leftamount').css("color", "#ef5350");
                lefToPayAmount = (-1) * lefToPayAmount;
                
                if ( leftInWallet < 0) {
                    leftInWallet = 0;
                }
            }
            leftAmount = lefToPayAmount;
            walletPayAmount = walletTotalAmount-leftInWallet;
            lefToPayAmount = currencySymbol +' '+ lefToPayAmount;
            leftInWallet = currencySymbol + leftInWallet;

            $("#p_method_walletsystem").parent().hide();
            
            if (!walletTotalAmount)
            {
                $("#p_method_walletsystem").parent().remove();
            }

            if (!$('.wk_ws_custom_checkbox').is(":checked"))
            {
                $('#p_method_walletsystem').prop('checked', false);
            } else {
                $(".wk_ws_payment_outer").show();
                $('.wk_ws_leftamount').text(lefToPayAmount);
                $('.wk_restamount').text(leftInWallet);
                $('#wk_ws_grandtotal').attr('value', walletPayAmount);
            }

            $( document ).delegate( ".wk_ws_custom_checkbox", "click", function () {
                if ($(this).is(":checked")){
                        $('#p_method_walletsystem').prop('checked', true);
                    } 
                    else {
                        $('#p_method_walletsystem').prop('checked', false);
                    }
                    
                $('.wk_ws_leftamount').text(lefToPayAmount);
                $('.wk_restamount').text(leftInWallet);
                $('#wk_ws_grandtotal').attr('value', walletPayAmount);

                if ( $(this).is(":checked") ) {
                    $(".wk_ws_payment_outer").show();
                } else {
                    $(".wk_ws_payment_outer").hide();
                    $('#wk_ws_grandtotal').attr('value', "");
                }
                
                if ((leftAmount > 0) && ($('#p_method_walletsystem').is(":checked")))
                {
                    $('#payment-continue').attr('disabled',true);
                    $('input:radio[name="payment[method]"]').click(function () {
                        $('#payment-continue').attr('disabled',false);
                    });
                }
                else {
                    $('#payment-continue').attr('disabled',false);
                    
                }
            });

            $( document ).delegate( "#p_method_walletsystem", "click", function() {
                $('.wk_ws_custom_checkbox').prop('checked', true);
                $(".wk_ws_payment_outer").show();

                if (leftAmount > 0)
                {
                    $('#payment-continue').attr('disabled',true);
                }
            });

            $( document ).delegate('input:radio[name="payment[method]"]', "click", function() {
                if($('.wk_ws_custom_checkbox').is(":checked") && (!leftAmount)){
                    $('.wk_ws_custom_checkbox').prop('checked', false);
                }

            });
        }
    }
);