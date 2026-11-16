{*
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
*}

{* Only show the popup, not the inline product videos block *}
{if isset($floating_videos) && $floating_videos|@count > 0}
    <div id="kb-video-popup" class="kb-video-popup kb-video-popup-open" data-floating-position="{$floating_position|default:'right'}">
        <div class="kb-video-popup-content" style="position:relative;">
            <button id="kb-video-popup-close" class="kb-video-popup-close" title="Close" type="button">
                &times;
            </button>
            <video id="kb-popup-video" width="200" height="360" controls autoplay loop muted style="cursor:pointer;" controlsList="nodownload">
                <source src="{$floating_videos.0.video_url}" type="video/mp4">
                {l s='Your browser does not support the video tag.' mod='kbvideomate'}
            </video>
        </div>
    </div>
    <div id="kb-video-popup-minimized" class="kb-video-popup-minimized" data-floating-position="{$floating_position|default:'right'}" style="display:none;">
        <video id="kb-popup-video-min" class="kb-popup-video-min" autoplay loop muted style="width:100px; height:100px; object-fit:cover; border-radius:50%; cursor:pointer;" controlsList="nodownload">
            <source src="{$floating_videos.0.video_url}" type="video/mp4">
            {l s='Your browser does not support the video tag.' mod='kbvideomate'}
        </video>
    </div>
    {* Fullscreen modal *}
    <div id="kb-video-modal" class="kb-video-modal" style="display:none;">
        <div class="kb-video-modal-content">
            <span id="kb-video-modal-close" class="kb-video-modal-close">&times;</span>
            <video id="kb-modal-video" controls autoplay loop muted style="max-width:90vw; max-height:90vh; display:block; margin:0 auto;" controlsList="nodownload">
                <source src="{$floating_videos.0.video_url}" type="video/mp4">
                {l s='Your browser does not support the video tag.' mod='kbvideomate'}
            </video>
        </div>
    </div>
{/if}

{* Output removeOnClose variable for JS, outside literal *}
<script>
var removeOnClose = {$floating_remove_on_close|intval};
</script>

{literal}
<style>
/* --- Floating Video Popup Styles --- */
.kb-video-popup {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    background: none;
    border-radius: 17px;
    padding: 8px 8px 0 8px;
    width: 220px;
    height: 390px;
}
.kb-video-popup[data-floating-position="left"] {
    left: 24px;
    right: auto;
}
.kb-video-popup[data-floating-position="right"] {
    right: 24px;
    left: auto;
}
.kb-video-popup-content {
    margin-top: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.kb-video-popup-content video {
    background: none !important;
    box-shadow: none !important;
    border-radius: 18px; /* More rounded video corners */
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.kb-video-popup-close {
    position: absolute;
    top: 9px;
    right: 9px;
    background: #fff;
    border: none;
    font-size: 2rem;
    color: #333;
    cursor: pointer;
    z-index: 2;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background 0.2s;
    outline: none;
}
.kb-video-popup-close:focus {
    outline: none;
}
.kb-video-popup-close:hover {
    background: #eee;
}
.kb-video-popup-minimized {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    width: 100px;
    height: 100px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 2px 16px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
/* Respect floating_position for minimized popup */
.kb-video-popup-minimized[data-floating-position="left"] {
    left: 24px;
    right: auto;
}
.kb-video-popup-minimized[data-floating-position="right"] {
    right: 24px;
    left: auto;
}
.kb-popup-video-min {
    width: 100px !important;
    height: 100px !important;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    background: #000;
}
/* --- Modal Styles (unchanged) --- */
.kb-video-modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.kb-video-modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.3);
}
.kb-video-modal-close {
    position: absolute;
    top: 8px;
    font-size: 2rem;
    color: #333;
    cursor: pointer;
    z-index: 2;
}
/* --- Responsive: Reduce margin on mobile --- */
@media (max-width: 600px) {
    .kb-video-popup {
        bottom: 6px !important; /* Less bottom margin on mobile */
        width: 160px !important; /* Slightly smaller popup on mobile */
        height: 290px !important;
        padding: 4px 4px 0 4px;
    }
    .kb-video-popup-content video {
        border-radius: 14px;
        width: 140px !important;
        height: 267px !important;
    }

    .kb-video-popup-close{
        right: 12px;
        top: 14px;
    }
    /* If floating_position is left, shift popup-content left for mobile */
    .kb-video-popup[data-floating-position="left"] .kb-video-popup-content {
        left: -34px;
        position: relative;
    }
    .kb-video-popup-minimized[data-floating-position="left"] {
        left: 6px !important;
        right: auto !important;
    }
    .kb-video-popup-minimized[data-floating-position="right"] {
        right: 6px !important;
        left: auto !important;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var popup = document.getElementById('kb-video-popup');
    var minimized = document.getElementById('kb-video-popup-minimized');
    var closeBtn = document.getElementById('kb-video-popup-close');
    var minVideo = document.getElementById('kb-popup-video-min');
    var video = document.getElementById('kb-popup-video');
    var modal = document.getElementById('kb-video-modal');
    var modalClose = document.getElementById('kb-video-modal-close');
    var modalVideo = document.getElementById('kb-modal-video');
    // Use global removeOnClose variable

    if (closeBtn) {
        closeBtn.onclick = function() {
            if (removeOnClose) {
                // Remove popup from DOM
                if (popup) popup.parentNode.removeChild(popup);
                if (minimized) minimized.parentNode.removeChild(minimized);
            } else {
                popup.style.display = 'none';
                minimized.style.display = 'flex';
            }
        };
    }
    if (minVideo) {
        minVideo.onclick = function() {
            popup.style.display = 'block';
            minimized.style.display = 'none';
        };
    }
    if (video) {
        video.onclick = function() {
            if (modal && modalVideo) {
                modal.style.display = 'flex';
                modalVideo.currentTime = video.currentTime;
                modalVideo.play();
            }
        };
    }
    if (modalClose) {
        modalClose.onclick = function() {
            modal.style.display = 'none';
            if (video) video.play();
        };
    }
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                if (video) video.play();
            }
        };
    }
});
</script>
{/literal} 