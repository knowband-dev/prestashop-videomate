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
(function kbVideoMateToggleInit() {

    var form = document.getElementById('kbvideomate_library_form');
    if (!form) {
        // Retry until HelperForm is rendered
        setTimeout(kbVideoMateToggleInit, 300);
        return;
    }

    function toggleGroup(fieldName, show) {
        var field = form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;

        var group = field.closest('.form-group');
        if (group) {
            group.style.display = show ? '' : 'none';
        }
    }

    /* ---------- Video Type ---------- */
    function applyVideoTypeVisibility() {
        var videoType = form.querySelector('[name="video_type"]')?.value;

        if (videoType === '1') {
            toggleGroup('video_file', true);
            toggleGroup('custom_video_url', false);
        } else if (videoType === '2') {
            toggleGroup('video_file', false);
            toggleGroup('custom_video_url', true);
        }
    }

    /* ---------- Redirection Type ---------- */
    function applyRedirectionVisibility() {
        var type = form.querySelector('[name="redirection_type"]')?.value;

        toggleGroup('product_id', false);
        toggleGroup('category_id', false);
        toggleGroup('custom_url', false);

        if (type === '1') {
            toggleGroup('product_id', true);
        } else if (type === '2') {
            toggleGroup('category_id', true);
        } else if (type === '3') {
            toggleGroup('custom_url', true);
        }
    }

    /* ---------- Bind Events ---------- */
    var videoTypeSelect = form.querySelector('[name="video_type"]');
    if (videoTypeSelect) {
        videoTypeSelect.onchange = applyVideoTypeVisibility;
    }

    var redirectionSelect = form.querySelector('[name="redirection_type"]');
    if (redirectionSelect) {
        redirectionSelect.onchange = applyRedirectionVisibility;
    }

    /* ---------- INITIAL RUN ---------- */
    applyVideoTypeVisibility();
    applyRedirectionVisibility();

})();

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
     * NEW FEATURE: FIELD VISIBILITY HANDLER (SAFE ADDITION)
     * ===================================================== */

    var videoForm = document.getElementById('kbvideomate_library_form');
if (!videoForm) {
    return;
}

function toggleField(name, show) {
    var field = videoForm.querySelector('[name="' + name + '"]');
    if (!field) return;

    var col = field.closest('.col-lg-8');
    if (!col) return;

    var formGroup = col.closest('.form-group');
    if (!formGroup) return;

    formGroup.style.display = show ? '' : 'none';
    field.disabled = !show;
}

function handleVideoTypeToggle() {
    var videoType = videoForm.querySelector('[name="video_type"]')?.value;

    if (videoType === '1') {
        toggleField('video_file', true);
        toggleField('custom_video_url', false);
    } else if (videoType === '2') {
        toggleField('video_file', false);
        toggleField('custom_video_url', true);
    }
}

function handleRedirectionTypeToggle() {
    var type = videoForm.querySelector('[name="redirection_type"]')?.value;

    toggleField('product_id', false);
    toggleField('category_id', false);
    toggleField('custom_url', false);

    if (type === '1') {
        toggleField('product_id', true);
    } else if (type === '2') {
        toggleField('category_id', true);
    } else if (type === '3') {
        toggleField('custom_url', true);
    }
}

// Initial state (page load / edit mode)
handleVideoTypeToggle();
handleRedirectionTypeToggle();

// Change handlers
videoForm.querySelector('[name="video_type"]')
    ?.addEventListener('change', handleVideoTypeToggle);

videoForm.querySelector('[name="redirection_type"]')
    ?.addEventListener('change', handleRedirectionTypeToggle);

    /* =====================================================
     * OLD JS STARTS (UNCHANGED)
     * ===================================================== */

    if (typeof velovalidation !== 'undefined') {

        videoForm.addEventListener('submit', function (e) {
            var isError = false;

            // Remove previous errors
            var errorMessages = videoForm.querySelectorAll('.kb_error_message');
            errorMessages.forEach(function (msg) {
                msg.remove();
            });

            var errorFields = videoForm.querySelectorAll('.kb_error_field');
            errorFields.forEach(function (field) {
                field.classList.remove('kb_error_field');
            });

            // Validate alias_name
            var aliasInput = videoForm.querySelector('input[name="alias_name"]');
            if (aliasInput) {
                var aliasErr = velovalidation.checkMandatory($(aliasInput));
                if (aliasErr !== true) {
                    isError = true;
                    aliasInput.classList.add('kb_error_field');
                    var errSpan = document.createElement('span');
                    errSpan.className = 'kb_error_message';
                    errSpan.style.color = 'red';
                    errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                    aliasInput.parentNode.appendChild(errSpan);
                }
            }

            /* Validate redirection fields based on Redirection Type.
	    * @modifier Himanshu Vishwakarma
            * @date 10-02-2026 
	    */
            var redirectionType = videoForm.querySelector('[name="redirection_type"]')?.value;
            if (redirectionType === '1') {
                var productSelect = videoForm.querySelector('[name="product_id"]');
                if (productSelect && (!productSelect.value || productSelect.value === '' || productSelect.value === '0')) {
                    isError = true;
                    var productCol = productSelect.closest('.col-lg-8') || productSelect.closest('.col-lg-9') || productSelect.parentNode;
                    /* Add error to Select2 selection only (not form-group) to avoid distorted UI. | 10-02-2026 */
                    var productSelect2 = productCol && productCol.querySelector('.select2-selection--single, .select2-selection--multiple');
                    if (productSelect2) productSelect2.classList.add('kb_error_field');
                    if (productCol) {
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.style.display = 'block';
                        errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                        productCol.appendChild(errSpan);
                    }
                }
            } else if (redirectionType === '2') {
                var categorySelect = videoForm.querySelector('[name="category_id"]');
                if (categorySelect && (!categorySelect.value || categorySelect.value === '' || categorySelect.value === '0')) {
                    isError = true;
                    var categoryCol = categorySelect.closest('.col-lg-8') || categorySelect.closest('.col-lg-9') || categorySelect.parentNode;
                    /* Add error to Select2 selection only (not form-group) to avoid distorted UI. | 10-02-2026 */
                    var categorySelect2 = categoryCol && categoryCol.querySelector('.select2-selection--single, .select2-selection--multiple');
                    if (categorySelect2) categorySelect2.classList.add('kb_error_field');
                    if (categoryCol) {
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.style.display = 'block';
                        errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                        categoryCol.appendChild(errSpan);
                    }
                }
            } else if (redirectionType === '3') {
                var customUrlInput = videoForm.querySelector('input[name="custom_url"]');
                if (customUrlInput && velovalidation.checkMandatory($(customUrlInput)) !== true) {
                    isError = true;
                    customUrlInput.classList.add('kb_error_field');
                    var errSpan = document.createElement('span');
                    errSpan.className = 'kb_error_message';
                    errSpan.style.color = 'red';
                    errSpan.innerText = typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required';
                    customUrlInput.parentNode.appendChild(errSpan);
                }
            }

            // Validate video_file ONLY when upload is selected
            var videoType = videoForm.querySelector('[name="video_type"]')?.value;
            var fileInput = videoForm.querySelector('input[name="video_file"]');

            /* Require video file only on add; when editing, existing video is kept if no new file. | 10-02-2026 */
            var isEdit = videoForm.querySelector('input[name="id_kbvideomate_library"]')?.value;
            if (videoType === '1' && fileInput && !isEdit) {

                if (fileInput.files.length === 0) {
                    isError = true;
                    fileInput.classList.add('kb_error_field');
                    var errSpan = document.createElement('span');
                    errSpan.className = 'kb_error_message';
                    errSpan.style.color = 'red';
                    errSpan.innerText = typeof kbvideomate_msg_video_required !== 'undefined' ? kbvideomate_msg_video_required : 'Video file is required';
                    fileInput.parentNode.appendChild(errSpan);
                } else {
                    var file = fileInput.files[0];

                    if (!/\.mp4$/i.test(file.name)) {
                        isError = true;
                        fileInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_only_mp4 !== 'undefined' ? kbvideomate_msg_only_mp4 : 'Only MP4 files are allowed';
                        fileInput.parentNode.appendChild(errSpan);
                    }

                    if (file.size > 50 * 1024 * 1024) {
                        isError = true;
                        fileInput.classList.add('kb_error_field');
                        var errSpan = document.createElement('span');
                        errSpan.className = 'kb_error_message';
                        errSpan.style.color = 'red';
                        errSpan.innerText = typeof kbvideomate_msg_file_size !== 'undefined' ? kbvideomate_msg_file_size : 'File size must be 50MB or less';
                        fileInput.parentNode.appendChild(errSpan);
                    }
                }
            }

            // Validate custom_video_url ONLY when URL selected
            if (videoType === '2') {
                var urlInput = videoForm.querySelector('[name="custom_video_url"]');
                if (urlInput && velovalidation.checkMandatory($(urlInput)) !== true) {
                    isError = true;
                    urlInput.classList.add('kb_error_field');
                    var errSpan = document.createElement('span');
                    errSpan.className = 'kb_error_message';
                    errSpan.style.color = 'red';
                    errSpan.innerText = typeof kbvideomate_msg_video_required !== 'undefined' ? kbvideomate_msg_video_required : 'Video URL is required';
                    urlInput.parentNode.appendChild(errSpan);
                }
            }

            if (isError) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

});
