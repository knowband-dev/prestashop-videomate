/**
 * KB VideoMate Admin Widget Form Validation
 * Enforces required fields and numeric checks for widget settings.
 * - Select Entity: required
 * - Video (from Library): required
 * - Widget Type: required
 * - If Widget Type is Carousel:
 *     - Max Visible: required, numeric, >0
 *     - Widget Heading: required
 *     - Auto Duration: required, numeric, >0
 * Shows inline error messages and prevents form submission if validation fails.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2024 Knowband
 */
'use strict';

document.addEventListener('DOMContentLoaded', function() {
    // Auto-select carousel widget type if none is selected (add mode)
    var widgetTypeRadios = document.querySelectorAll('input[name="widget_type"]');
    var anySelected = false;
    widgetTypeRadios.forEach(function(radio) {
        if (radio.checked) {
            anySelected = true;
        }
    });
    if (!anySelected && widgetTypeRadios.length > 0) {
        // Select the carousel radio by default
        var carouselRadio = document.getElementById('widget_carousel');
        if (carouselRadio) {
            carouselRadio.checked = true;
        }
    }

    // Ensure velovalidation is loaded
    if (typeof velovalidation !== 'undefined') {
        // Get the admin config form
        var configForm = document.getElementById('kbvideomate_form');
        if (configForm) {
            configForm.addEventListener('submit', function(e) {
                var isError = false;
                
                // Remove previous error messages
                var prevErrors = configForm.querySelectorAll('.kb_error_message');
                prevErrors.forEach(function(el) { el.remove(); });
                var prevErrorFields = configForm.querySelectorAll('.kb_error_field');
                prevErrorFields.forEach(function(el) { el.classList.remove('kb_error_field'); });

                // --- Validate Page Selection ---
                var pageSelect = configForm.querySelector('select[name="page"]');
                if (pageSelect) {
                    var pageErr = velovalidation.checkMandatory($(pageSelect));
                    if (pageErr !== true) {
                        isError = true;
                        pageSelect.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                        pageSelect.parentNode.appendChild(errSpan);
                    }
                }

                // --- Validate Entity Selection ---
                var entitySelect = configForm.querySelector('#kb-entity-select');
                var pageValue = pageSelect ? pageSelect.value : '';
                // Only validate entity if required for the selected page
                if (entitySelect && entitySelect.style.display !== 'none' && ['product', 'category', 'manufacturer', 'supplier', 'cms'].includes(pageValue)) {
                    var entityErr = velovalidation.checkMandatory($(entitySelect));
                    if (entityErr !== true) {
                        isError = true;
                        entitySelect.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                        entitySelect.parentNode.appendChild(errSpan);
                    }
                }

                /*
                * Validate Video Selection.
                * Bug fix: an exact-match selector for "id_kbvideomate_library[]" missed
                * the field entirely for the Floating widget type, where widget-form.js
                * renames it to the bracket-less "id_kbvideomate_library" - required-video
                * validation silently never ran for that widget type. Match by prefix
                * instead, same as widget-form.js's own selector, so it finds the field
                * regardless of its current single/multi name.
                * 2026-08-05
                */
                var videoSelect = configForm.querySelector('select[name^="id_kbvideomate_library"]');
                if (videoSelect) {
                    /*
                    * Bug fix: read the native DOM selection directly instead of jQuery's
                    * $(videoSelect).val() - that goes through Select2's own wrapper, which
                    * can fall out of sync with the real underlying <select> (e.g. after
                    * widget-form.js's select2("destroy")/reinit when switching widget_type).
                    * options[].selected always reflects the browser's real state regardless
                    * of any Select2 UI desync, so this can't be fooled the same way.
                    * 2026-08-05
                    */
                    var selectedValues = [];
                    for (var i = 0; i < videoSelect.options.length; i++) {
                        if (videoSelect.options[i].selected && videoSelect.options[i].value !== '') {
                            selectedValues.push(videoSelect.options[i].value);
                        }
                    }
                    var isVideoSelected = selectedValues.length > 0;
                    if (!isVideoSelected) {
                        isError = true;
                        videoSelect.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_select_video !== 'undefined' ? kbvideomate_msg_select_video : 'Please select a video from the library';
                        videoSelect.parentNode.appendChild(errSpan);
                    }
                }

                // --- Validate Widget Type ---
                var widgetTypeRadios = configForm.querySelectorAll('input[name="widget_type"]');
                var widgetTypeSelected = false;
                widgetTypeRadios.forEach(function(radio) {
                    if (radio.checked) {
                        widgetTypeSelected = true;
                    }
                });
                if (!widgetTypeSelected) {
                    isError = true;
                    var widgetTypeGroup = configForm.querySelector('.widget-type-group');
                    if (widgetTypeGroup) {
                        widgetTypeGroup.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_select_widget !== 'undefined' ? kbvideomate_msg_select_widget : 'Please select a widget type';
                        widgetTypeGroup.parentNode.appendChild(errSpan);
                    }
                }

                // --- Validate Max Visible for Carousel ---
                var maxVisibleInput = $(configForm).find('#kbvideomate-max-visible')[0];
                var widgetType = configForm.querySelector('input[name="widget_type"]:checked');
                var widgetTypeValue = widgetType ? widgetType.value : '';
                // Only validate if visible AND widget type is carousel
                if (
                    maxVisibleInput &&
                    $(maxVisibleInput).is(':visible') &&
                    widgetTypeValue === 'carousel'
                ) {
                    var value = maxVisibleInput.value.trim();
                    // Numeric-only, positive integer validation
                    if (!value) {
                        isError = true;
                        maxVisibleInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_max_visible !== 'undefined' ? kbvideomate_msg_max_visible : 'Please enter the maximum number of visible videos';
                        maxVisibleInput.parentNode.appendChild(errSpan);
                    } else if (!/^\d+$/.test(value) || parseInt(value, 10) < 1) {
                        isError = true;
                        maxVisibleInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_max_visible_valid !== 'undefined' ? kbvideomate_msg_max_visible_valid : 'Please enter a valid positive integer';
                        maxVisibleInput.parentNode.appendChild(errSpan);
                    }
                }

                // --- Validate Autoplay Duration for Carousel ---
                var autoplayInput = $(configForm).find('#kbvideomate-carousel-autoplay')[0];
                // Only validate if visible AND widget type is carousel
                if (
                    autoplayInput &&
                    $(autoplayInput).is(':visible') &&
                    widgetTypeValue === 'carousel'
                ) {
                    var value = autoplayInput.value.trim();
                    // Numeric-only, positive integer validation
                    if (!value) {
                        isError = true;
                        autoplayInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_autoplay !== 'undefined' ? kbvideomate_msg_autoplay : 'Please enter the autoplay duration in seconds';
                        autoplayInput.parentNode.appendChild(errSpan);
                    } else if (!/^\d+$/.test(value) || parseInt(value, 10) < 1) {
                        isError = true;
                        autoplayInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_autoplay_valid !== 'undefined' ? kbvideomate_msg_autoplay_valid : 'Please enter a valid positive integer';
                        autoplayInput.parentNode.appendChild(errSpan);
                    }
                }

                // Prevent form submit if any error
                if (isError) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
    }
});