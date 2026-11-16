/**
 * [2020] PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2020 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') {
        return;
    }

    var $ = window.jQuery;

    $(function () {
        var $entitySelect = $('#kb-ai-products-select');
        var $entityGroup = $entitySelect.closest('.form-group');
        var searchTimeout = null;
        var currentSearchTerm = '';

        if (!$entitySelect.length) {
            return;
        }

        function getToken() {
            return typeof window.kbvideomate_token !== 'undefined' ? window.kbvideomate_token : '';
        }

        /*
         * Debounce helper (copied from widget entity logic, scoped to AI Video form).
         * 10-02-2026
         */
        function debounce(func, wait) {
            return function () {
                var context = this;
                var args = arguments;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    func.apply(context, args);
                }, wait);
            };
        }

        /*
         * Attach debounced search handler to the Select2 search input for AI products field only.
         * 10-02-2026
         */
        function attachProductInputHandler() {
            $('.kb_ai_products_select .select2-input').off('input.kb_ai keyup.kb_ai');

            var debouncedSearch = debounce(function (term) {
                currentSearchTerm = term;
                if (term.length >= 1) {
                    fetchProducts(term);
                } else {
                    if ($entitySelect.hasClass('select2-offscreen')) {
                        // no-op; Select2 manages the UI reset
                    }
                    $entitySelect.select2({ width: '100%' });
                    attachProductInputHandler();
                }
            }, 500);

            $('.kb_ai_products_select .select2-input').on('input.kb_ai keyup.kb_ai', function () {
                var term = $(this).val();
                debouncedSearch(term);
            });
        }

        /*
         * Fetch products via AdminKbVideoMateAjax and populate options (type fixed to 'product').
         * @modifier Himanshu Vishwakarma
         * @date 10-02-2026
         */
        function fetchProducts(term) {
            var selectedOptions = [];
            $entitySelect.find('option:selected').each(function () {
                selectedOptions.push({
                    id: $(this).val(),
                    text: $(this).text()
                });
            });

            var existingIds = [];
            $entitySelect.find('option').each(function () {
                existingIds.push($(this).val());
            });

            $.ajax({
                url: 'index.php?controller=AdminKbVideoMateAjax&token=' + encodeURIComponent(getToken()),
                dataType: 'json',
                data: {
                    q: term,
                    type: 'product'
                },
                success: function (data) {
                    if (!data.items || !data.items.length) {
                        return;
                    }

                    var selectedValues = $entitySelect.val() || [];
                    var newOptionsMap = {};

                    $.each(data.items, function (i, item) {
                        if (existingIds.indexOf(item.id.toString()) === -1) {
                            newOptionsMap[item.id] = item.text;
                        }
                    });

                    $.each(data.items, function (i, item) {
                        if (existingIds.indexOf(item.id.toString()) === -1) {
                            var option = new Option(
                                item.text,
                                item.id,
                                false,
                                $.inArray(item.id, selectedValues) > -1
                            );
                            $entitySelect.append(option);
                        }
                    });

                    $.each(selectedOptions, function (i, opt) {
                        if (!newOptionsMap[opt.id]) {
                            var option = new Option(opt.text, opt.id, true, true);
                            $entitySelect.append(option);
                        }
                    });

                    $entitySelect.select2({
                        width: '100%',
                        placeholder: 'Search products...',
                        multiple: true,
                        allowClear: true
                    });

                    setTimeout(function () {
                        $entitySelect.select2('open');
                        setTimeout(function () {
                            var $input = $('.kb_ai_products_select .select2-input');
                            $input.val(currentSearchTerm);
                            $input.focus();
                        }, 100);
                    }, 0);

                    attachProductInputHandler();
                }
            });
        }

        /*
         * Initialize AI product selector as searchable Select2 multi-select.
         * @modifier Himanshu Vishwakarma
         * @date 10-02-2026
         */
        function initProductField() {
            if ($entitySelect.hasClass('select2-offscreen')) {
                $entitySelect.select2('destroy');
            }

            $entitySelect.attr('multiple', 'multiple');
            if ($entityGroup && $entityGroup.length) {
                $entityGroup.show();
            }

            $entitySelect.empty();
            currentSearchTerm = '';

            $entitySelect.addClass('kb_ai_products_select');

            $entitySelect.select2({
                width: '100%',
                placeholder: 'Search products...',
                multiple: true,
                allowClear: true
            });

            attachProductInputHandler();
        }

        initProductField();
    });
});
