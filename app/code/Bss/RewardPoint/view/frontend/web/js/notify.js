/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require(
    ['jquery'],
    function ($) {
        $(document).ready(function () {
            $("#rewardpoint-notify").submit(function () {
                var notifyBalance = $("input[name='notify_balance']").prop( "checked" );
                notifyBalance = (notifyBalance === true) ? 1 : 0;
                var notifyExpiration = $("input[name='notify_expiration']").prop( "checked" );
                notifyExpiration = (notifyExpiration === true) ? 1 : 0;
                var customerId = $("input[name='customer_id']").val();
                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: {notify_balance:notifyBalance,notify_expiration:notifyExpiration,customer_id:customerId},
                    showLoader: true,
                    success: function (response) {
                        console.log(response.output);
                    },
                });
                return false;
            });
        });
    });
