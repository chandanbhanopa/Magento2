define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'docusignpayment',
                component: 'MangoIt_DocuSignPayment/js/view/payment/method-renderer/docusignpayment'
            }
        );
        return Component.extend({});
    }
);