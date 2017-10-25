/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            customerAttributesCheckout: 'MangoIt_Checkoutfield/js/view/checkout',
            customerAttributesCheckoutGuest: 'MangoIt_Checkoutfield/js/view/checkout-guest',
            customerAttributesAccount: 'MangoIt_Checkoutfield/js/view/register',
            "calendar":             "mage/calendar",
            "Magento_Checkout/js/model/shipping-save-processor/default" : "MangoIt_Checkoutfield/js/view/shipping-save-processor-default-override"
        }
    },
    config: {mixins: {
        'Magento_Checkout/js/action/set-shipping-information': {
            'MangoIt_Checkoutfield/js/action/set-shipping-information-mixin': true
        }
    }
}
};
