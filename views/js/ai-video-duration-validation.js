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

    /* Attach submit listener using event delegation so we catch form submit regardless of when form was added. | 10-02-2026 */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.tagName || form.tagName.toLowerCase() !== 'form') return;
        if (!form.querySelector('input[name*="video_duration"]') || !form.querySelector('textarea[name*="custom_prompt"]')) return;

        var isError = false;

        /* Remove previous errors (same pattern as ai-video-settings.js and video-form-validation.js). | 10-02-2026 */
        var errorMessages = form.querySelectorAll('.kb_error_message');
        for (var i = 0; i < errorMessages.length; i++) {
            errorMessages[i].remove();
        }
        var errorFields = form.querySelectorAll('.kb_error_field');
        for (var j = 0; j < errorFields.length; j++) {
            errorFields[j].classList.remove('kb_error_field');
        }

        /* --- Products (mandatory) --- */
        var productsSelect = form.querySelector('#kb-entity-select') || form.querySelector('select[name*="id_products"]');
        if (productsSelect) {
            var selectedOpts = productsSelect.multiple
                ? Array.prototype.filter.call(productsSelect.options, function (o) { return o.selected && o.value; })
                : (productsSelect.value ? [productsSelect.options[productsSelect.selectedIndex]] : []);
            if (!selectedOpts.length) {
                isError = true;
                var productCol = productsSelect.closest('.col-lg-8') || productsSelect.closest('.col-lg-9') || productsSelect.parentNode;
                var productSelect2 = productCol && productCol.querySelector('.select2-selection--single, .select2-selection--multiple');
                if (productSelect2) productSelect2.classList.add('kb_error_field');
                if (productCol) showFieldError(productCol, getRequiredMsg());
            }
        }

        /* --- Custom Prompt (mandatory) --- */
        var promptInput = form.querySelector('textarea[name="custom_prompt"]') || form.querySelector('textarea[name*="custom_prompt"]');
        if (promptInput) {
            var promptVal = (promptInput.value || '').trim();
            if (promptVal === '') {
                isError = true;
                promptInput.classList.add('kb_error_field');
                showFieldError(promptInput.closest('.col-lg-8') || promptInput.parentNode, getRequiredMsg());
            }
        }

        /* --- Video Duration (mandatory + platform range) --- */
        var durationInput = form.querySelector('input[name*="video_duration"]');
        if (durationInput) {
            var durationVal = (durationInput.value || '').trim();
            var durationNum = parseInt(durationVal, 10);
            if (durationVal === '' || isNaN(durationNum) || durationNum < 0) {
                isError = true;
                durationInput.classList.add('kb_error_field');
                showFieldError(durationInput.closest('.col-lg-8') || durationInput.parentNode, getRequiredMsg());
            } else {
                var platform = typeof kbvideomate_ai_platform !== 'undefined' ? String(kbvideomate_ai_platform).toLowerCase() : '';
                var model = typeof kbvideomate_ai_model !== 'undefined' ? String(kbvideomate_ai_model) : '';
                var isVeo2 = model.indexOf('veo-2') !== -1;
                var min = 4, max = 8, msgKey = 'veo3';
                /* Platform is stored as 'gemini' or 'chatgpt' (Configuration option id), not 'openai'. | 10-02-2026 */
                if (platform === 'gemini') {
                    if (isVeo2) { min = 5; max = 8; msgKey = 'veo2'; } else { min = 4; max = 8; msgKey = 'veo3'; }
                } else if (platform === 'chatgpt' || platform === 'openai') {
                    min = 4; max = 12; msgKey = 'openai';
                }
                if (durationNum < min || durationNum > max) {
                    isError = true;
                    durationInput.classList.add('kb_error_field');
                    showFieldError(durationInput.closest('.col-lg-8') || durationInput.parentNode, getDurationMsg(msgKey));
                }
            }
        }

        if (isError) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    function getRequiredMsg() {
        return typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
    }

    function getDurationMsg(key) {
        if (key === 'veo2' && typeof kbvideomate_duration_veo2 !== 'undefined') return kbvideomate_duration_veo2;
        if (key === 'veo3' && typeof kbvideomate_duration_veo3 !== 'undefined') return kbvideomate_duration_veo3;
        if (key === 'openai' && typeof kbvideomate_duration_openai !== 'undefined') return kbvideomate_duration_openai;
        return 'Duration is out of allowed range.';
    }

    function showFieldError(container, msg) {
        if (!container) return;
        var errSpan = document.createElement('span');
        errSpan.className = 'kb_error_message';
        errSpan.style.color = '#f13340';
        errSpan.style.display = 'block';
        errSpan.style.marginTop = '5px';
        errSpan.innerText = msg;
        container.appendChild(errSpan);
    }
});
