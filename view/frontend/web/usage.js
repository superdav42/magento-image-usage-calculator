/**
 * @api
 */
define([
    'jquery',
    'priceBox',
    'jquery/ui'
], function ($, priceBox) {
    'use strict';
    var self;

    $.widget('mage.usage', {
        options: {
            priceHolderSelector: '#product-options-wrapper .price-box'
        },
        hidden: true,

        /** @inheritdoc */
        _create: function () {
            self = this;

            this.element.find(this.options.categorySelectElement).on('change', function () {
                self.element.find(".category-container").hide().find('select, input, textarea').prop('disabled', true);
                self.element.find(".usage-container").hide();
                self.element.find("#category_container_" + $(this).val()).show()
                    .find('.usage-select-box').val('').prop('disabled', false);
                self.__addHashToURL();
            });

            $('#category_container_previous').change(function () {
                var values = $('#usage_previous_usages').val().split(' - ');
                var elements = $('#downloadable-usages-list .usage-container').find('select, input, textarea');

                // Setting first two values (category and its usage)
                $('#usage_category').val(values[0]);
                $('[name="usage_id\\[' + values[0] + '\\]"]').val(values[1]).show().prop('disabled', false).closest('.category-container').show();

                // removing first two values from the array
                values.splice(0, 2);

                //setting values for rest of the elements (select, input, textarea)
                values.forEach(function (item, index) {
                    var kvp = item.split(':');
                    $('#downloadable-usages-list [name*="\\[' + kvp[0] + '\\]"]').val(kvp[1]).show().prop('disabled', false).closest('.usage-container').show();
                });
                $(this).hide().find('.usage-select-box').prop('disabled', true);
                self._reloadPrice();
                self.__addHashToURL();
            });

            this.element.find('select, input, textarea').on('change', this._reloadPrice);

            this.element.find('.help-text').hide();

            this.element.find('label').on('click', function (e) {
                e.preventDefault();
                $(this).closest('.field').find('.help-text').toggle()
                var $a = $(this).find('a');
                var current = $a.html();
                $a.html($a.data('less'));
                $a.data('less', current);
            });

            this.element.find(".usage-select-box").on('change', function () {
                self.element.find(".usage-container").hide().find('select, input, textarea').prop('disabled', true);
                self.element.find("#usage_container_" + $(this).val()).show().find('select, input, textarea').prop('disabled', false);
                self.__addHashToURL();
            });

            $('.usages-container-inner').hide().find('select, input, textarea').prop('disabled', true);
            $('#usage-button, #usage-button-close').on('click', function (e) {
                e.preventDefault();
                $('#maincontent .product.info.detailed').toggle();
                $('#maincontent .product-info-main .product-info-main > div:not(.product-add-form)').toggle();
                $('#product-options-wrapper > div > :not(.product-info-price):not(.usages-container)').toggle();
                $('.usages-container-inner').toggle();
                $('#usage-button').toggle();
                if (self.hidden) {
                    self.element.find(self.options.categorySelectElement).prop('disabled', false).val('').trigger('change');
                    $('#previously_usage_category').prop('disabled', false);
                    location.hash = "#license";
                } else {
                    $('.usages-container-inner').find('select, input, textarea').prop('disabled', true);
                    $(self.options.priceHolderSelector + ', .product-options-bottom').hide();
                    location.hash = "";
                }

                self.hidden = !self.hidden;
            });

            $(self.options.priceHolderSelector).priceBox('setDefault', {
                'basePrice': {
                    'amount': 0.0,
                    'adjustments': []
                },
                'finalPrice': {
                    'amount': 0.0,
                    'adjustments': []
                },
                'oldPrice': {
                    'amount': 0.0,
                    'adjustments': []
                }
            });

            $(self.options.priceHolderSelector + ', .product-options-bottom').hide();

            $('.usage-container select').on('change', function () {
                self.__addHashToURL();
            });

            $('.usage-container input').change(function () {
                self.__addHashToURL();
            });

            if (location.hash.substr(0, 8) === '#license') {
                var properties = location.hash.substr(9, location.hash.length);
                $('#usage-button').click();
                if (properties.length) {
                    self._loadProperties(properties);
                }
            }

        },

        /**
         * Reload product price with selected link price included
         * @private
         */
        _reloadPrice: function () {
            var finalPrice = 0,
                basePrice = 0,
                $usage,
                $selected,
                terms,
                categoryId,
                haveActive = false;

            self.element.find().show();

            categoryId = self.element.find(self.options.categorySelectElement).val();

            $usage = self.element.find("#category_container_" + categoryId + ' .usage-select-box option:selected');

            finalPrice = basePrice = parseFloat($usage.attr('price'));
            terms = $usage.data('terms');

            if (basePrice) {
                self.element.find("#category_container_" + categoryId).removeClass('active');
            } else if (categoryId) {
                self.element.find('.field.active').removeClass('active');
                self.element.find("#category_container_" + categoryId).addClass('active');
            } else {
                self.element.find('.usages-container-inner > .control > .field').addClass('active');
            }

            self.element.find("#usage_container_" + $usage.attr('value') + ' select[name*=options]').each(function (index, select) {
                $selected = $(select).find('option:selected');
                if (!$selected.attr('price') && !haveActive) {
                    $(select).closest('.field').addClass('active');
                    haveActive = true;
                } else {
                    $(select).closest('.field.active').removeClass('active');
                }

                finalPrice *= parseFloat($selected.attr('price')) / 100;
                terms = terms.replace(
                    '(' + select.title + ')',
                    '<strong>' + $selected.text() + '</strong>'
                );
            });

            self.element.find("#usage_container_" + $usage.attr('value') + ' input').each(function (index, input) {
                if (!$(input).val() && !haveActive) {
                    $(input).closest('.field').addClass('active');
                    haveActive = true;
                } else {
                    $(input).closest('.field.active').removeClass('active');
                }
                terms = terms.replace(
                    '(' + $(input).data('title') + ')',
                    '<strong>' + $(input).val() + '</strong>'
                );
            });

            if (isNaN(finalPrice)) {
                finalPrice = 0;
            }

            if (95 !== ((finalPrice * 100) % 100)) {
                finalPrice = Math.round(finalPrice);
            }

            $(self.options.priceHolderSelector).trigger('updatePrice', {
                'prices': {
                    'finalPrice': {
                        'amount': finalPrice
                    },
                    'basePrice': {
                        'amount': basePrice
                    }
                }
            });

            if (finalPrice <= 0) {
                $(self.options.priceHolderSelector + ', .product-options-bottom').hide();
                $('#usages-advice-container').html('');
            } else {
                $('#usages-advice-container').html(terms);
                $(self.options.priceHolderSelector + ', .product-options-bottom').show();
            }
        },

        /**
         * Load all the options according to url
         * @param properties
         * @private
         */
        _loadProperties: function (properties) {
            var decodeProperties = decodeURI(properties).split('&');
            decodeProperties.forEach(function (option) {
                var kvp = option.split('=');
                kvp[0] = kvp[0].replace('[', '\\[').replace(']', '\\]');
                self.element.find('[name=' + kvp[0] + ']').val(kvp[1]).show().prop('disabled', false).change();
                self.element.find('[name=' + kvp[0] + ']').closest('.field').show();
                self.element.find('[name=' + kvp[0] + ']').closest('.usage-container').show();
            });
            self._reloadPrice();
        },

        __addHashToURL: function () {
            var hashVal = '#license';
            $('#product_addtocart_form input, #product_addtocart_form select').each(function (index) {
                var isDisabled = $(this).is(':disabled') || $(this).attr('type') == 'hidden' || !$(this).is(':visible') || $(this).val() == null || !$(this).val();
                if (!isDisabled) {
                    hashVal += '&' + $(this).attr('name') + '=' + $(this).val();
                }
            });
            location.hash = encodeURI(hashVal);
        }
    });

    return $.mage.usage;
});
