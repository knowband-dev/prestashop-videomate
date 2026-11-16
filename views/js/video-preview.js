/**
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

// Admin video preview logic for kbvideomate
// Handles preview modal for video library

function normalizeVideoUrl(url) {
    if (typeof url !== 'string' || !url) return url;
    return url.replace(/([^\/])modules\//, '$1/modules/');
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle video preview button click
    document.body.addEventListener('click', function(e) {
        var target = e.target.closest('.video-preview-btn');
        if (target) {
            e.preventDefault();
            e.stopPropagation(); // Prevent row click/edit
            var videoUrl = target.getAttribute('data-video-url');
            var videoElement = document.getElementById('kbvideomate-preview-video');
            var modal = document.getElementById('video-preview-modal');
            if (!videoElement || !modal || !videoUrl) return;
            videoElement.src = normalizeVideoUrl(videoUrl);
            videoElement.load();
            // Show modal (Bootstrap 3/4 compatible)
            if (typeof $ !== 'undefined' && typeof $(modal).modal === 'function') {
                $(modal).modal('show');
            } else {
                modal.style.display = 'block';
                modal.classList.add('in');
            }
        }
    });

    // Handle modal close (Bootstrap fallback)
    var modal = document.getElementById('video-preview-modal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            var videoElement = document.getElementById('kbvideomate-preview-video');
            videoElement.pause();
            videoElement.currentTime = 0;
        });
        // Fallback for manual close (if not using Bootstrap modal)
        var closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                modal.classList.remove('in');
                var videoElement = document.getElementById('kbvideomate-preview-video');
                if (videoElement) {
                    videoElement.pause();
                    videoElement.currentTime = 0;
                }
            });
        }
    }

    var tableIds = ['form-kbvideomate_library', 'form-kbvideomate_ai_video'];
    tableIds.forEach(function(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.addEventListener('click', function(e) {
                // Allow clicks on action buttons, status toggle, and preview button
                if (e.target.closest('.video-preview-btn, .btn, .button, a, input[type="checkbox"], label')) {
                    // If it's a preview button, handle the preview
                    var previewBtn = e.target.closest('.video-preview-btn');
                    if (previewBtn) {
                        e.preventDefault();
                        e.stopPropagation();
                        var videoUrl = previewBtn.getAttribute('data-video-url');
                        var videoElement = document.getElementById('kbvideomate-preview-video');
                        var modal = document.getElementById('video-preview-modal');
                        if (videoElement && modal && videoUrl) {
                            videoElement.src = normalizeVideoUrl(videoUrl);
                            videoElement.load();
                            if (typeof $ !== 'undefined' && typeof $(modal).modal === 'function') {
                                $(modal).modal('show');
                            } else {
                                modal.style.display = 'block';
                                modal.classList.add('in');
                            }
                        }
                    }
                    return true; // Allow the click to proceed
                }

                // Block clicks on the row itself to prevent navigation
                var tr = e.target.closest('tr');
                if (tr && !e.target.closest('td:first-child')) { // Allow clicks on the first column (checkbox)
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true); // Use capture phase to override PrestaShop's default
        }
    });
}); 