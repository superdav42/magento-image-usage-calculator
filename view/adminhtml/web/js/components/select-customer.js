/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedCustomers = config.selectedCustomers,
            usageCustomer = $H(selectedCustomers),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;

        $('in_usage_customers').value = Object.toJSON(usageCustomer);

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerUsageCustomer(grid, element, checked) {
            if (checked) {
                    usageCustomer.set(element.value, element.value);
                }
            else {
                usageCustomer.unset(element.value);
            }
            $('in_usage_customers').value = Object.toJSON(usageCustomer);
            grid.reloadParams = {
                'selected_customers[]': usageCustomer.keys()
            };
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function usageCustomerRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Initialize category product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function usageCustomersRowInit(grid, row) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0]
        }

        gridJsObject.rowClickCallback = usageCustomerRowClick;
        gridJsObject.initRowCallback = usageCustomersRowInit;
        gridJsObject.checkboxCheckCallback = registerUsageCustomer;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                usageCustomersRowInit(gridJsObject, row);
            });
        }
    };
});
