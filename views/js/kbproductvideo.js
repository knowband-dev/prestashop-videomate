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

function normalizeVideoUrl(url) {
    if (typeof url !== 'string' || !url) return url;
    return url.replace(/([^\/])modules\//, '$1/modules/');
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize video functionality
    initKbProductVideos();
});

function initKbProductVideos() {
    // Add click event listeners to video thumbnails
    document.querySelectorAll('.kb-video-thumb-img').forEach(function(thumb) {
        thumb.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleVideoThumbnailClick(this);
        });
    });

    // Handle video thumbnail click
    function handleVideoThumbnailClick(thumbElement) {
        const videoType = thumbElement.dataset.videoType;
        const videoContainer = createVideoContainer(thumbElement);
        
        // Find the main image container
        const mainImageContainer = document.querySelector('.product-cover');
        if (!mainImageContainer) return;

        // Find or create the video wrapper
        let videoWrapper = mainImageContainer.querySelector('.kb-product-video-wrapper');
        if (!videoWrapper) {
            videoWrapper = document.createElement('div');
            videoWrapper.className = 'kb-product-video-wrapper';
            mainImageContainer.appendChild(videoWrapper);
        }

        // Set video wrapper height
        videoWrapper.style.height = '253px';

        // Hide the main product image
        const mainImage = mainImageContainer.querySelector('.js-qv-product-cover');
        if (mainImage) {
            mainImage.style.display = 'none';
        }

        // Clear any existing videos
        videoWrapper.innerHTML = '';
        
        // Add the new video
        videoWrapper.appendChild(videoContainer);

        // Auto-play the video
        if (window.kb_product_video_autoplay) {
            playVideo(videoContainer, videoType);
        }

        // Handle modal view if needed
        const modalBtn = mainImageContainer.querySelector('.layer');
        if (modalBtn) {
            modalBtn.style.display = 'none';
        }

        // Remove selected class from other thumbnails and add to video thumbnail
        document.querySelectorAll('.js-thumb-selected').forEach(thumb => {
            thumb.classList.remove('js-thumb-selected');
        });
        thumbElement.classList.add('js-thumb-selected');
    }

    // Create video container based on type
    function createVideoContainer(thumbElement) {
        const container = document.createElement('div');
        container.className = 'kb-product-video-container js-kb-product-video';
        
        switch(thumbElement.dataset.videoType) {
            case 'youtube':
                container.innerHTML = `
                    <iframe width="100%" 
                            height="100%" 
                            src="https://www.youtube.com/embed/${thumbElement.dataset.videoId}?autoplay=1&mute=1&loop=1&playlist=${thumbElement.dataset.videoId}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>`;
                break;
                
            case 'vimeo':
                container.innerHTML = `
                    <iframe width="100%" 
                            height="100%" 
                            src="https://player.vimeo.com/video/${thumbElement.dataset.videoId}?autoplay=1&muted=1&loop=1" 
                            frameborder="0" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen>
                    </iframe>`;
                break;
                
            case 'html5':
                container.innerHTML = `
                    <video width="100%" height="100%" controls autoplay muted loop>
                        <source src="${normalizeVideoUrl(thumbElement.dataset.videoUrl || '')}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>`;
                break;
        }
        
        return container;
    }

    // Play video based on type
    function playVideo(container, type) {
        switch(type) {
            case 'html5':
                const video = container.querySelector('video');
                if (video) {
                    video.play();
                }
                break;
            // YouTube and Vimeo videos auto-play through URL parameters
        }
    }

    // Handle product image thumbnails click
    document.querySelectorAll('.js-thumb').forEach(function(thumb) {
        if (!thumb.classList.contains('kb-video-thumb-img')) {
            thumb.addEventListener('click', function(e) {
                // Check if this thumbnail is already selected
                if (this.classList.contains('js-thumb-selected')) {
                    const videoWrapper = document.querySelector('.kb-product-video-wrapper');
                    if (videoWrapper) {
                        videoWrapper.style.height = '0';
                        videoWrapper.innerHTML = '';
                    }
                }
                restoreMainImage();
            });
        }
    });

    // Restore main product image
    function restoreMainImage() {
        const mainImageContainer = document.querySelector('.product-cover');
        if (!mainImageContainer) return;

        const mainImage = mainImageContainer.querySelector('.js-qv-product-cover');
        const videoWrapper = mainImageContainer.querySelector('.kb-product-video-wrapper');
        const modalBtn = mainImageContainer.querySelector('.layer');

        if (mainImage) {
            mainImage.style.display = '';
        }
        if (videoWrapper) {
            videoWrapper.style.height = '0';
            videoWrapper.innerHTML = '';
        }
        if (modalBtn) {
            modalBtn.style.display = '';
        }
    }

    // Handle modal close
    const modalClose = document.querySelector('.modal-close');
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            restoreMainImage();
        });
    }

    /* Set video as main image on load when toggle is enabled.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026 
    */
    var videoAsMainContainer = document.getElementById('kb-product-video-thumbs');
    if (videoAsMainContainer && videoAsMainContainer.getAttribute('data-video-as-main-image') === '1') {
        var firstVideoThumb = videoAsMainContainer.querySelector('.kb-video-thumb-img');
        if (firstVideoThumb) {
            setTimeout(function() { firstVideoThumb.click(); }, 100);
        }
    }
} 