 define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/template',
    'text!Magento_Sales/templates/order/create/shipping/reload.html',
    'text!Magento_Sales/templates/order/create/payment/reload.html',
    'mage/translate',
    'prototype',
    'Magento_Catalog/catalog/product/composite/configure',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Sales/order/create/form',
         "Magento_Catalog/catalog/product/composite/configure"

], function (jQuery, confirm, alert, template, shippingTemplate, paymentTemplate) {


     jQuery.async('#order-items', (function () {

         var searchButtonId = 'add_bulk_products',
             searchButton = new ControlButton(jQuery.mage.__('Add Bulk Products'), searchButtonId),
             searchAreaId = 'order-search',
             listType = 'product_bulk_add';
         searchButton.onClick = function () {
             productConfigure.setConfirmCallback(
                 listType,
                 function () {
                     new Ajax.Request(productConfigure.listTypes.product_bulk_add.skusToIds, {
                         parameters: {
                             skus: productConfigure.blockConfirmed.getElementsBySelector('[name=skus]').first().value
                         },
                         onSuccess: function (transport) {
                             var response = transport.responseText;

                             if (response.isJSON()) {
                                 response = response.evalJSON();

                                 if (response.error || ! response.ids) {
                                     productConfigure.blockMsg.show();
                                     productConfigure.blockMsgError.innerHTML = response.message || 'Unable to fetch products for skus';
                                     if(productConfigure.blockCancelBtn) {
                                         productConfigure.blockCancelBtn.hide();
                                     }
                                     productConfigure.setConfirmCallback(listType, null);
                                     productConfigure._showWindow();
                                 } else {

                                     if ( response.warning ) {
                                         alert(response.warning);
                                     }

                                     var itemsFilter = [];
                                     var fieldsPrepare = {};
                                     var bogusId = $(productConfigure._getConfirmedBlockId(listType, 5555));

                                     for( const productId of response.ids ) {
                                         itemsFilter.push(productId);
                                         var paramKey = 'item[' + productId + '][' + 'qty' + ']';

                                         if (fieldsPrepare[paramKey]) {
                                             fieldsPrepare[paramKey]++;
                                            continue;
                                         } else {
                                             fieldsPrepare[paramKey] = 1
                                         }

                                         var newConfirmed = productConfigure._getConfirmedBlockId(listType, productId);

                                         productConfigure.blockConfirmed.insert(new Element('div', {
                                            id: newConfirmed
                                        }));
                                         $newConfirmed = $(newConfirmed);
                                         // clone confirmed to form
                                        var mageData = null;

                                         bogusId.childElements().each(function (elm) {
                                             if (! elm.hasClassName('js-skip-clone')) {

                                                var cloned = elm.cloneNode(true);

                                                // <select> does not keep it's value when cloned.
                                                var selectElems = elm.getElementsByTagName('select');

                                                var clonnedSelects = cloned.getElementsByTagName('select');
                                                for(let i = 0;i < clonnedSelects.length; i++) {
                                                    clonnedSelects[i].value = selectElems[i].value;
                                                }

                                                if (elm.mageData) {
                                                    cloned.mageData = elm.mageData;
                                                    mageData = elm.mageData;
                                                }

                                                 $newConfirmed.insert(cloned);
                                             }
                                         });

                                         // Execute scripts
                                        if (mageData && mageData.scripts) {
                                            productConfigure.restorePhase = true;

                                            try {
                                                mageData.scripts.map(function (script) {
                                                    return eval(script);
                                                });
                                            } catch (e) {

                                            }
                                            productConfigure.restorePhase = false;
                                        }

                                     }
                                     bogusId.remove();

                                    var area = ['search', 'items', 'shipping_method', 'totals', 'giftmessage', 'billing_method'];
                                    order.productConfigureSubmit(listType, area, fieldsPrepare, itemsFilter);
                                 }
                             }
                         }
                     });

                 }
             )

             // add additional fields before triggered submit
             productConfigure.setBeforeSubmitCallback(listType, function () {

                 // create additional fields
                 var params = {}
                 params.reset_shipping = true;

                 order.prepareParams(params);
                 for (var i in params) {
                     if (params[i] === null) {
                         unset(params[i]);
                     } else if (typeof (params[i]) == 'boolean') {
                         params[i] = params[i] ? 1 : 0;
                     }
                 }
                 var fields = [];
                 for (var name in params) {
                     fields.push(new Element('input', {type: 'hidden', name: name, value: params[name]}));
                 }
                 productConfigure.addFields(fields);
             });
             // response handler
             productConfigure.setOnLoadIFrameCallback(listType, function (response) {
                 var areas = ['items', 'shipping_method', 'billing_method', 'totals', 'giftmessage'];

                 if (!response.ok) {
                     return;
                 }

                 order.loadArea(areas, true);

             });

             productConfigure.showItemConfiguration('product_bulk_add', 5555);

         };

         jQuery.async('#order-items .admin__page-section-title', (function () {
             order.itemsArea.addControlButton(searchButton);
         }));

         var cartButtonId = 'add_cart_products',
             cartButton = new ControlButton(jQuery.mage.__('Add All To Cart'), searchButtonId);
         cartButton.onClick = function () {
             jQuery('#order-items_grid .col-actions select.admin__control-select').val('cart');
             order.itemsUpdate();
         }
         jQuery.async('#order-items .order-discounts', (function () {
             order.itemsArea.addControlButton(cartButton);
         }));

     }));
});
