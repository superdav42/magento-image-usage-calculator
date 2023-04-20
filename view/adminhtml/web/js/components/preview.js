/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'ko'
], function (_, Abstract, registry, ko) {
    'use strict';

    return Abstract.extend({
        defaults: {
            listens: {
                '${ $.provider }:data': 'dataChanged'
            },
            links: {
                allData: '${ $.provider }:data'
            }
        },
        calculatedPrice: ko.observable(),
        calculatedSize: ko.observable(),

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            this.selectedUsageOptions = [];
            this.dataChanged(this.allData);
            return this;
        },

        selectedUsageOptionsChanged: function(changed) {
            var price = this.allData.price;
            var size_id = this.allData.size_id;
            for( var index in this.allData.usage.options ) {
                if (this.allData.usage.options[index].type !== 'drop_down') {
                    continue
                }
                var selectedOption = this.selectedUsageOptions[index]()
                if ( ! selectedOption ) {
                    this.calculatedPrice('');
                    return;
                }
                if ( ! selectedOption.price ) {
                    continue;
                }
                if ( selectedOption.size_id && selectedOption.size_id !== '0') {
                    size_id = selectedOption.size_id;
                }
                price *= parseFloat(selectedOption.price.replace(/,/g, '')) / 100;
            }
            var size = registry.get('index = size_id').indexedOptions[size_id].label;
            this.calculatedSize(size);
            this.calculatedPrice('$'+price.toFixed(2));
        },

        dataChanged: function( data ) {
            if ( ! this.selectedUsageOptions || ! data.usage || ! data.usage.options ) {
                return;
            }
            for( var i = this.selectedUsageOptions.length; i < data.usage.options.length; i++ ) {
                var ob = ko.observable();
                ob.subscribe(this.selectedUsageOptionsChanged.bind(this));
                this.selectedUsageOptions.push(ob)
            }
        },

        /**
         * {@inheritdoc}
         */
        initObservable: function () {
            this._super()
                .observe(['content']);

            return this;
        }
    });
});
