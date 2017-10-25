/**
 * @author MangoIt Team
 * @package MangoIt_Checkoutfield
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/media'
], function (_, registry, Media) {
    'use strict';

    return Media.extend({
        defaults: {
            elementTmpl: 'MangoIt_Checkoutfield/form/element/media',
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        initialize: function () {
            this._super();
            if (this.value()) {
                this.value(this.path + this.value());
            }

            return this;
        }

    });
});
