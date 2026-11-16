/**
 * VideoMate Module JS
 * Handles video loading, error handling, and carousel autoplay
 */

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

// This file is adapted from kbvideowise.js for kbvideomate
// Handles carousel, floating widget, and video validation for kbvideomate

$(document).ready(function() {
    // Initialize carousel if present
    if ($('.kbvideomate-carousel').length) {
        // Carousel logic can be added here if needed
    }

    // Initialize floating widget if present
    if ($('.kbvideomate-floating-widget').length) {
        // Floating widget logic can be added here if needed
    }

    // Library Add/Edit Video Form Validation
    var $form = $('#kbvideomate_library_form');
    $form.on('submit', function(e) {
        var isError = false;
        // Remove previous errors
        $form.find('.kb_error_message').remove();
        $form.find('.kb_error_field').removeClass('kb_error_field');

        // Validate alias_name
        var $alias = $form.find('input[name="alias_name"]');
        if ($alias.length && !$alias.val().trim()) {
            isError = true;
            $alias.addClass('kb_error_field');
            $alias.after('<span class="kb_error_message" style="color:red;">' + (typeof kbvideomate_msg_required !== 'undefined' ? kbvideomate_msg_required : 'This field is required') + '</span>');
        }

        // Validate video_file (only on add, not edit)
        var $file = $form.find('input[type="file"][name="video_file"]');
        var isEdit = $form.find('input[name="id_kbvideomate_library"]').length > 0;
        if ($file.length && (!isEdit || $file.val())) {
            var file = $file[0].files[0];
            if (!file) {
                isError = true;
                $file.addClass('kb_error_field');
                $file.after('<span class="kb_error_message" style="color:red;">' + (typeof kbvideomate_msg_video_required !== 'undefined' ? kbvideomate_msg_video_required : 'Video file is required') + '</span>');
            } else {
                // Check file type
                if (file.type !== 'video/mp4' && !file.name.match(/\.mp4$/i)) {
                    isError = true;
                    $file.addClass('kb_error_field');
                    $file.after('<span class="kb_error_message" style="color:red;">' + (typeof kbvideomate_msg_only_mp4 !== 'undefined' ? kbvideomate_msg_only_mp4 : 'Only MP4 files are allowed') + '</span>');
                }
                // Check file size (<= 50MB)
                if (file.size > 50 * 1024 * 1024) {
                    isError = true;
                    $file.addClass('kb_error_field');
                    $file.after('<span class="kb_error_message" style="color:red;">' + (typeof kbvideomate_msg_file_size !== 'undefined' ? kbvideomate_msg_file_size : 'File size must be 50MB or less') + '</span>');
                }
            }
        }

        if (isError) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
  // Video loading indicator and error handling
  var videos = document.querySelectorAll('.kbvideomate-video');
  videos.forEach(function(video) {
    // Add loading spinner
    var spinner = document.createElement('div');
    spinner.className = 'kbvideomate-spinner';
    spinner.style.display = 'none';
    spinner.innerHTML = '<span style="display:inline-block;width:24px;height:24px;border:3px solid #ccc;border-top:3px solid #333;border-radius:50%;animation:kbvmspin 1s linear infinite;"></span>';
    video.parentNode.insertBefore(spinner, video);

    video.addEventListener('waiting', function() {
      spinner.style.display = 'block';
    });
    video.addEventListener('playing', function() {
      spinner.style.display = 'none';
    });
    video.addEventListener('error', function() {
      spinner.style.display = 'none';
      var errorMsg = document.createElement('div');
      errorMsg.className = 'kbvideomate-error';
      errorMsg.textContent = (typeof kbvideomate_msg_video_failed !== 'undefined' ? kbvideomate_msg_video_failed : 'Video failed to load.');
      errorMsg.style.color = '#d00';
      video.parentNode.appendChild(errorMsg);
    });
  });

  // Carousel autoplay (if enabled)
  var carousels = document.querySelectorAll('.kbvideomate-carousel');
  carousels.forEach(function(carousel) {
    var autoplay = parseInt(carousel.getAttribute('data-autoplay'), 10);
    if (autoplay > 0) {
      var videoEls = carousel.querySelectorAll('video');
      var idx = 0;
      function playNext() {
        if (videoEls.length === 0) return;
        videoEls.forEach(function(v, i) { v.pause(); v.currentTime = 0; });
        videoEls[idx].play();
        idx = (idx + 1) % videoEls.length;
        setTimeout(playNext, autoplay);
      }
      playNext();
    }
  });

  // Floating widget close button
  var closeBtns = document.querySelectorAll('.kbvideomate-close');
  closeBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var widget = btn.closest('.kbvideomate-floating-widget');
      if (widget) widget.style.display = 'none';
    });
  });
});

// Spinner animation
var style = document.createElement('style');
style.innerHTML = '@keyframes kbvmspin {0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}';
document.head.appendChild(style); 