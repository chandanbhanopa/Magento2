/**
 * @author MangoIt Team
 * @package MangoIt_Checkoutfield
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/textarea'
], function (ko, _, utils, TextArea) {
    'use strict';

    return TextArea.extend({

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            return this;
        }

    });
});
