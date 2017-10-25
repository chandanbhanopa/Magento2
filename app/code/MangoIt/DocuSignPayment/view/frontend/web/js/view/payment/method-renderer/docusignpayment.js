define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'MangoIt_DocuSignPayment/payment/docusignpayment'
            }
        });
    }
);