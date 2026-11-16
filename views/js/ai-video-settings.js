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
    // PrestaShop switch renders two radio inputs for the toggle
    var aiToggles = document.querySelectorAll('input[name="KBVIDEOMATE_AI_VIDEO_ENABLED"]');
    var platformSelect = document.querySelector('select[name="KBVIDEOMATE_AI_VIDEO_PLATFORM"]');
    var modelSelect = document.querySelector('select[name="KBVIDEOMATE_AI_VIDEO_MODEL"]');
    var geminiApiKeyInput = document.querySelector('input[name="KBVIDEOMATE_AI_VIDEO_API_KEY_GEMINI"]');
    var openAiApiKeyInput = document.querySelector('input[name="KBVIDEOMATE_AI_VIDEO_API_KEY_OPENAI"]');

    function getFieldContainer(el) {
        if (!el) {
            return null;
        }
        if (typeof el.closest === 'function') {
            var group = el.closest('.form-group');
            if (group) {
                return group;
            }
        }
        return el.parentElement;
    }

    var platformGroup = getFieldContainer(platformSelect);
    var modelGroup = getFieldContainer(modelSelect);
    var geminiApiKeyGroup = getFieldContainer(geminiApiKeyInput);
    var openAiApiKeyGroup = getFieldContainer(openAiApiKeyInput);
    var cronBatchInput = document.querySelector('input[name="KBVIDEOMATE_AI_VIDEO_CRON_BATCH"]');
    var cronBatchGroup = getFieldContainer(cronBatchInput);
    var imageTypeSelect = document.querySelector('select[name="KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE"]');
    var imageTypeGroup = getFieldContainer(imageTypeSelect);

    var modelOptions = {
        gemini: [
            { value: 'models/veo-2.0-generate-001', label: 'Gemini Veo 2.0' },
            { value: 'models/veo-3.0-generate-001', label: 'Gemini Veo 3.0 (models/veo-3.0-generate-001)' },
            { value: 'models/veo-3.0-fast-generate-001', label: 'Gemini Veo 3.0 Fast (models/veo-3.0-fast-generate-001)' },
            { value: 'models/veo-3.1-generate-preview', label: 'Gemini Veo 3.1 Preview (models/veo-3.1-generate-preview)' },
            { value: 'models/veo-3.1-fast-generate-preview', label: 'Gemini Veo 3.1 Fast Preview (models/veo-3.1-fast-generate-preview)' }
        ],
        chatgpt: [
            { value: 'sora-2', label: 'OpenAI Sora 2 (sora-2)' },
            { value: 'sora-2-pro', label: 'OpenAI Sora 2 Pro (sora-2-pro)' }
        ]
    };

    function rebuildModelOptions() {
        if (!platformSelect || !modelSelect) {
            return;
        }
        var platform = platformSelect.value || 'gemini';
        var current = modelSelect.value;
        var options = modelOptions[platform] || [];

        while (modelSelect.firstChild) {
            modelSelect.removeChild(modelSelect.firstChild);
        }

        options.forEach(function (opt) {
            var o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.label;
            modelSelect.appendChild(o);
        });

        var hasMatch = false;
        options.forEach(function (opt) {
            if (opt.value === current) {
                hasMatch = true;
            }
        });
        if (hasMatch) {
            modelSelect.value = current;
        }
    }

    function isAiEnabled() {
        var enabled = false;
        aiToggles.forEach(function (toggle) {
            if (toggle.checked && toggle.value === '1') {
                enabled = true;
            }
        });
        return enabled;
    }

    function syncVisibility() {
        var enabled = isAiEnabled();
        [platformGroup, modelGroup, cronBatchGroup, imageTypeGroup].forEach(function (g) {
            if (g) {
                g.style.display = enabled ? '' : 'none';
            }
        });
        // Toggle which API key field is visible based on selected platform
        if (geminiApiKeyGroup && openAiApiKeyGroup) {
            if (!enabled) {
                geminiApiKeyGroup.style.display = 'none';
                openAiApiKeyGroup.style.display = 'none';
            } else {
                var platform = platformSelect ? platformSelect.value : 'gemini';
                if (platform === 'chatgpt') {
                    geminiApiKeyGroup.style.display = 'none';
                    openAiApiKeyGroup.style.display = '';
                } else {
                    geminiApiKeyGroup.style.display = '';
                    openAiApiKeyGroup.style.display = 'none';
                }
            }
        }
        if (enabled) {
            rebuildModelOptions();
        }
    }

    if (platformSelect) {
        platformSelect.addEventListener('change', function () {
            rebuildModelOptions();
            syncVisibility();
        });
    }
    aiToggles.forEach(function (toggle) {
        toggle.addEventListener('change', syncVisibility);
    });

    syncVisibility();

    /*
    * Copy cron URL to clipboard when Copy button is clicked.
    * 10-02-2026
    */
    var copyBtn = document.querySelector('.kb-cron-copy-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var url = this.getAttribute('data-cron-url');
            if (!url) { return; }
            var original = copyBtn.innerHTML;
            var showCopied = function () {
                copyBtn.innerHTML = '<i class="icon-ok"></i> Copied';
                copyBtn.classList.add('copied');
                setTimeout(function () {
                    copyBtn.innerHTML = original;
                    copyBtn.classList.remove('copied');
                }, 2000);
            };
            if (typeof navigator.clipboard !== 'undefined' && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(showCopied);
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    showCopied();
                } finally {
                    document.body.removeChild(ta);
                }
            }
        });
    }
});

