/*
 * [2024] PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2024 Knowband
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  PrestaShop Module
 */

/*
 * Autocomplete functionality for product and category fields in library form
 * Uses Select2 with manual AJAX search via AdminKbVideoMateAjaxController
 * Compatible with PrestaShop's Select2 version (doesn't use ajax option)
 * 20-12-2024
 */

// Wait for jQuery and Select2 to be available
(function() {
    var maxRetries = 100; // Maximum 10 seconds (100 * 100ms)
    var retryCount = 0;
    var select2Loaded = false;
    var searchTimeout = null;
    
    function loadSelect2() {
        // Check if jQuery is available
        var jQueryAvailable = false;
        var $ = null;
        
        if (typeof jQuery !== 'undefined') {
            $ = jQuery;
            jQueryAvailable = true;
        } else if (typeof window.jQuery !== 'undefined') {
            $ = window.jQuery;
            jQueryAvailable = true;
        } else if (typeof window.$ !== 'undefined' && window.$.fn) {
            $ = window.$;
            window.jQuery = $; // Make jQuery available
            jQueryAvailable = true;
        }
        
        if (!jQueryAvailable) {
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(loadSelect2, 100);
                return;
            } else {
                console.error('jQuery is not available after maximum retries');
                return;
            }
        }
        
        // jQuery is available, now check if Select2 is loaded
        // Select2 should be loaded by PrestaShop via addJqueryPlugin
        if (typeof $.fn.select2 !== 'undefined') {
            // Select2 is already loaded
            select2Loaded = true;
            initLibraryAutocomplete($);
        } else {
            // Select2 not loaded yet, wait for it
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(loadSelect2, 100);
            } else {
                console.error('Select2 is not available after maximum retries. Make sure addJqueryPlugin(\'select2\') is called.');
            }
        }
    }
    
    function initLibraryAutocomplete($) {
        
        // Initialize when DOM is ready
        if (typeof $ === 'undefined') {
            console.error('jQuery ($) is not defined');
            return;
        }
        
        $(document).ready(function() {
            var $productSelect = $('#kb-product-select');
            var $categorySelect = $('#kb-category-select');
            
            // Get AJAX token
            function getToken() {
                return typeof kbvideomate_ajax_token !== 'undefined' ? kbvideomate_ajax_token : '';
            }
            
            // Debounce function to limit AJAX calls
            function debounce(func, wait) {
                return function() {
                    var context = this;
                    var args = arguments;
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }
            
            // Fetch products via AJAX and populate select options
            function fetchProducts(term) {
                if (!term || term.length < 1) {
                    return;
                }
                
                // Store currently selected option
                var selectedOption = null;
                $productSelect.find("option:selected").each(function() {
                    selectedOption = {
                        id: $(this).val(),
                        text: $(this).text()
                    };
                });
                
                // Get existing IDs to prevent duplicates
                var existingIds = [];
                $productSelect.find('option').each(function() {
                    existingIds.push($(this).val());
                });
                
                $.ajax({
                    url: 'index.php?controller=AdminKbVideoMateAjax&token=' + encodeURIComponent(getToken()),
                    dataType: 'json',
                    data: {
                        q: term,
                        type: 'product'
                    },
                    success: function(data) {
                        if (data.items && data.items.length) {
                            // Clear existing options except selected one
                            $productSelect.find('option').not(':selected').remove();
                            
                            // Add new options from AJAX
                            $.each(data.items, function(i, item) {
                                if (!existingIds.includes(item.id.toString())) {
                                    var option = new Option(item.text, item.id, false, false);
                                    $productSelect.append(option);
                                }
                            });
                            
                            // Re-add selected option if it exists
                            if (selectedOption) {
                                var selectedExists = $productSelect.find('option[value="' + selectedOption.id + '"]').length > 0;
                                if (!selectedExists) {
                                    var option = new Option(selectedOption.text, selectedOption.id, true, true);
                                    $productSelect.append(option);
                                }
                            }
                            
                            // Reinitialize select2 (ensure no multiple option)
                            // Destroy first to avoid conflicts
                            if ($productSelect.hasClass('select2-hidden-accessible') || $productSelect.data('select2')) {
                                $productSelect.select2('destroy');
                            }
                            // Ensure multiple is not set
                            $productSelect.removeAttr('multiple');
                            $productSelect.prop('multiple', false);
                            $productSelect.select2({
                                width: '100%',
                                placeholder: 'Search for a product...',
                                allowClear: true
                            });
                            
                            // Restore search term and open dropdown
                            setTimeout(function() {
                                $productSelect.select2('open');
                                setTimeout(function() {
                                    var $input = $('.select2-search input');
                                    if ($input.length) {
                                        $input.val(term);
                                        $input.focus();
                                    }
                                }, 100);
                            }, 0);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Product search failed:', error);
                    }
                });
            }
            
            // Fetch categories via AJAX and populate select options
            function fetchCategories(term) {
                if (!term || term.length < 1) {
                    return;
                }
                
                // Store currently selected option
                var selectedOption = null;
                $categorySelect.find("option:selected").each(function() {
                    selectedOption = {
                        id: $(this).val(),
                        text: $(this).text()
                    };
                });
                
                // Get existing IDs to prevent duplicates
                var existingIds = [];
                $categorySelect.find('option').each(function() {
                    existingIds.push($(this).val());
                });
                
                $.ajax({
                    url: 'index.php?controller=AdminKbVideoMateAjax&token=' + encodeURIComponent(getToken()),
                    dataType: 'json',
                    data: {
                        q: term,
                        type: 'category'
                    },
                    success: function(data) {
                        if (data.items && data.items.length) {
                            // Clear existing options except selected one
                            $categorySelect.find('option').not(':selected').remove();
                            
                            // Add new options from AJAX
                            $.each(data.items, function(i, item) {
                                if (!existingIds.includes(item.id.toString())) {
                                    var option = new Option(item.text, item.id, false, false);
                                    $categorySelect.append(option);
                                }
                            });
                            
                            // Re-add selected option if it exists
                            if (selectedOption) {
                                var selectedExists = $categorySelect.find('option[value="' + selectedOption.id + '"]').length > 0;
                                if (!selectedExists) {
                                    var option = new Option(selectedOption.text, selectedOption.id, true, true);
                                    $categorySelect.append(option);
                                }
                            }
                            
                            // Reinitialize select2 (ensure no multiple option)
                            // Destroy first to avoid conflicts
                            if ($categorySelect.hasClass('select2-hidden-accessible') || $categorySelect.data('select2')) {
                                $categorySelect.select2('destroy');
                            }
                            // Ensure multiple is not set
                            $categorySelect.removeAttr('multiple');
                            $categorySelect.prop('multiple', false);
                            $categorySelect.select2({
                                width: '100%',
                                placeholder: 'Search for a category...',
                                allowClear: true
                            });
                            
                            // Restore search term and open dropdown
                            setTimeout(function() {
                                $categorySelect.select2('open');
                                setTimeout(function() {
                                    var $input = $('.select2-search input');
                                    if ($input.length) {
                                        $input.val(term);
                                        $input.focus();
                                    }
                                }, 100);
                            }, 0);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Category search failed:', error);
                    }
                });
            }
            
            // Attach input handler to Select2 search input for products
            // Using class-based selector like entity-field.js
            function attachProductInputHandler() {
                // Remove previous handlers
                $('.kb-product-autocomplete .select2-input').off('input.product-search keyup.product-search');
                
                // Create debounced search function
                var debouncedSearch = debounce(function(term) {
                    if (term && term.length >= 1) {
                        fetchProducts(term);
                    }
                }, 300);
                
                // Attach handlers using class selector (works with Select2 dropdown appended to body)
                $('.kb-product-autocomplete .select2-input').on('input.product-search keyup.product-search', function() {
                    var term = $(this).val();
                    debouncedSearch(term);
                });
            }
            
            // Attach input handler to Select2 search input for categories
            // Using class-based selector like entity-field.js
            function attachCategoryInputHandler() {
                // Remove previous handlers
                $('.kb-category-autocomplete .select2-input').off('input.category-search keyup.category-search');
                
                // Create debounced search function
                var debouncedSearch = debounce(function(term) {
                    if (term && term.length >= 1) {
                        fetchCategories(term);
                    }
                }, 300);
                
                // Attach handlers using class selector (works with Select2 dropdown appended to body)
                $('.kb-category-autocomplete .select2-input').on('input.category-search keyup.category-search', function() {
                    var term = $(this).val();
                    debouncedSearch(term);
                });
            }
            
            // Initialize product autocomplete (single selection)
            if ($productSelect.length) {
                // Destroy existing Select2 instance if any
                if ($productSelect.hasClass('select2-hidden-accessible') || $productSelect.data('select2')) {
                    $productSelect.select2('destroy');
                }
                // Ensure it's a single select
                $productSelect.removeAttr('multiple');
                $productSelect.prop('multiple', false);
                // Add class for easier targeting
                $productSelect.addClass('kb-product-autocomplete');
                $productSelect.select2({
                    width: '100%',
                    placeholder: 'Search for a product...',
                    allowClear: true
                });
                
                // Pre-select product if editing
                if (typeof kbSelectedProduct !== 'undefined' && kbSelectedProduct) {
                    var option = new Option(kbSelectedProduct.name, kbSelectedProduct.id, true, true);
                    $productSelect.append(option).trigger('change');
                }
                
                // Attach search handler after a short delay to ensure Select2 is initialized
                setTimeout(function() {
                    attachProductInputHandler();
                }, 200);
                
                // Reattach handler when select2 opens (Select2 v3.x event)
                $productSelect.on('select2-open', function() {
                    setTimeout(attachProductInputHandler, 150);
                });
                
                // Also listen for Select2 v4.x event
                $productSelect.on('select2:open', function() {
                    setTimeout(attachProductInputHandler, 150);
                });
            }
            
            // Initialize category autocomplete (single selection)
            if ($categorySelect.length) {
                // Destroy existing Select2 instance if any
                if ($categorySelect.hasClass('select2-hidden-accessible') || $categorySelect.data('select2')) {
                    $categorySelect.select2('destroy');
                }
                // Remove multiple attribute if present (must be done before Select2 init)
                $categorySelect.removeAttr('multiple');
                // Ensure it's a single select
                $categorySelect.prop('multiple', false);
                // Add class for easier targeting
                $categorySelect.addClass('kb-category-autocomplete');
                $categorySelect.select2({
                    width: '100%',
                    placeholder: 'Search for a category...',
                    allowClear: true
                });
                
                // Pre-select category if editing (single selection)
                if (typeof kbSelectedCategories !== 'undefined' && Array.isArray(kbSelectedCategories) && kbSelectedCategories.length > 0) {
                    // Take the first category if multiple are provided
                    var category = kbSelectedCategories[0];
                    var option = new Option(category.name, category.id, true, true);
                    $categorySelect.append(option).trigger('change');
                }
                
                // Attach search handler after a short delay to ensure Select2 is initialized
                setTimeout(function() {
                    attachCategoryInputHandler();
                }, 200);
                
                // Reattach handler when select2 opens (Select2 v3.x event)
                $categorySelect.on('select2-open', function() {
                    setTimeout(attachCategoryInputHandler, 150);
                });
                
                // Also listen for Select2 v4.x event
                $categorySelect.on('select2:open', function() {
                    setTimeout(attachCategoryInputHandler, 150);
                });
            }
        });
    }
    
    // Start loading process - wait for jQuery first
    if (window.addEventListener) {
        window.addEventListener('load', function() {
            setTimeout(loadSelect2, 200);
        }, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', function() {
            setTimeout(loadSelect2, 200);
        });
    }
    
    // Also try on DOM ready as a fallback
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadSelect2, 300);
        });
    } else {
        // If DOM is already loaded, try immediately with a delay
        setTimeout(loadSelect2, 300);
    }
})();
