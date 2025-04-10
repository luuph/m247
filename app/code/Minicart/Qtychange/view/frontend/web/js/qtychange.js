define(['jquery', 'mage/url', 'Magento_Customer/js/customer-data'], function ($, urlBuilder, customerData) {
    function qtyChange(event) {
        console.log('qtyChange triggered');

        let target = $(event.target);
        let inputField, newQty;

        if (target.val() === 'inc') {
            inputField = target.prev();
            newQty = parseFloat(inputField.val()) + 1;
        } else {
            inputField = target.next();
            newQty = Math.max(parseFloat(inputField.val()) - 1, 1);
        }
        console.log(newQty);

        inputField.val(newQty);
        let productSku = inputField.attr('data-cart-item-id');
        console.log(productSku);

        $.ajax({
            url: urlBuilder.build('minicart_qtychange/cart/updatecart') + '?sku=' + productSku + '&qty=' + newQty,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    let updatedSubtotal = response.subtotal;
                    $('.subtotal .price-wrapper').html('KWD ' + updatedSubtotal);
                    console.log('Cart updated successfully.');
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function removeItem(event) {
        console.log('removeItem triggered');
        
        let target = $(event.target);
        // let productSku = target.attr('data-cart-item-id');
        let productSku = $(event.target).closest('.remove-item').attr('data-cart-item-id');
        console.log(productSku);
        
        $.ajax({
            url: urlBuilder.build('minicart_qtychange/cart/remove') + '?sku=' + productSku,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    console.log('Item removed successfully.');
                    let sections = ['cart'];
                    customerData.reload(sections, true);
                    target.closest('li.item').remove();
                    $('.subtotal .price-wrapper').html('KWD ' + response.subtotal);
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    window.qtyChange = qtyChange;
    window.removeItem = removeItem;
});
