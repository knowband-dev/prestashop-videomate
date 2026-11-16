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

{if isset($manufacturer_videos) && $manufacturer_videos|@count > 0}
    {* --- Floating Widget --- *}
    {if isset($floating_widget) && $floating_widget}
        <div class="kbvw-floating-widget" style="display: none;">
            <div class="kbvw-floating-widget-header">
                <span class="kbvw-floating-widget-title"></span>
                <button class="kbvw-floating-widget-close" aria-label="Close">&times;</button>
            </div>
            <div class="kbvw-floating-widget-content">
                <video class="kbvw-floating-widget-video" controls>
                    <source src="" type="video/mp4">
                    {l s='Your browser does not support the video tag.' mod='kbvideomate'}
                </video>
            </div>
        </div>
    {/if}

    {* --- Carousel Widget --- *}
    {if isset($carousel_widget) && $carousel_widget}
        <div class="kbvw-carousel-widget">
            {if isset($widget_heading) && $widget_heading}
                <h3 class="kbvw-carousel-widget-heading">{$widget_heading|escape:'html':'UTF-8'}</h3>
            {/if}
            <div class="kbvw-carousel-widget-inner">
                {foreach from=$manufacturer_videos key=key item=video}
                    <div class="kbvw-carousel-widget-item" data-index="{$key}">
                        <div class="kbvw-carousel-widget-thumbnail">
                            <video class="kbvw-carousel-widget-video" muted loop>
                                <source src="{$video.video_url}" type="video/mp4">
                                {l s='Your browser does not support the video tag.' mod='kbvideomate'}
                            </video>
                            <button class="kbvw-carousel-widget-play" aria-label="Play">&#9658;</button>
                        </div>
                        <div class="kbvw-carousel-widget-info">
                            <h4 class="kbvw-carousel-widget-title">{$video.title|escape:'html':'UTF-8'}</h4>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

    {* --- Grid Widget --- *}
    {if isset($grid_widget) && $grid_widget}
        <div class="kbvw-grid-widget">
            {if isset($widget_heading) && $widget_heading}
                <h3 class="kbvw-grid-widget-heading">{$widget_heading|escape:'html':'UTF-8'}</h3>
            {/if}
            <div class="kbvw-grid-widget-inner">
                {foreach from=$manufacturer_videos key=key item=video}
                    <div class="kbvw-grid-widget-item">
                        <div class="kbvw-grid-widget-thumbnail">
                            <video class="kbvw-grid-widget-video" muted loop>
                                <source src="{$video.video_url}" type="video/mp4">
                                {l s='Your browser does not support the video tag.' mod='kbvideomate'}
                            </video>
                            <button class="kbvw-grid-widget-play" aria-label="Play">&#9658;</button>
                        </div>
                        <div class="kbvw-grid-widget-info">
                            <h4 class="kbvw-grid-widget-title">{$video.title|escape:'html':'UTF-8'}</h4>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

    {* --- JavaScript for Widgets --- *}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var floatingWidget = document.querySelector('.kbvw-floating-widget');
        var carouselWidget = document.querySelector('.kbvw-carousel-widget');
        var gridWidget = document.querySelector('.kbvw-grid-widget');
        var currentVideo = null;

        // Function to open floating widget
        function openFloatingWidget(videoUrl, videoTitle) {
            if (!floatingWidget) return;
            var video = floatingWidget.querySelector('video');
            var title = floatingWidget.querySelector('.kbvw-floating-widget-title');
            video.src = videoUrl;
            title.textContent = videoTitle;
            floatingWidget.style.display = 'block';
            video.play();
            currentVideo = video;
        }

        // Function to close floating widget
        function closeFloatingWidget() {
            if (!floatingWidget) return;
            if (currentVideo) {
                currentVideo.pause();
                currentVideo = null;
            }
            floatingWidget.style.display = 'none';
        }

        // Event listeners for carousel widget
        if (carouselWidget) {
            var carouselItems = carouselWidget.querySelectorAll('.kbvw-carousel-widget-item');
            carouselItems.forEach(function(item) {
                var playButton = item.querySelector('.kbvw-carousel-widget-play');
                var video = item.querySelector('video');
                var title = item.querySelector('.kbvw-carousel-widget-title').textContent;

                playButton.addEventListener('click', function() {
                    closeFloatingWidget();
                    openFloatingWidget(video.src, title);
                });

                video.addEventListener('mouseover', function() {
                    this.play();
                });

                video.addEventListener('mouseout', function() {
                    this.pause();
                    this.currentTime = 0;
                });
            });
        }

        // Event listeners for grid widget
        if (gridWidget) {
            var gridItems = gridWidget.querySelectorAll('.kbvw-grid-widget-item');
            gridItems.forEach(function(item) {
                var playButton = item.querySelector('.kbvw-grid-widget-play');
                var video = item.querySelector('video');
                var title = item.querySelector('.kbvw-grid-widget-title').textContent;

                playButton.addEventListener('click', function() {
                    closeFloatingWidget();
                    openFloatingWidget(video.src, title);
                });

                video.addEventListener('mouseover', function() {
                    this.play();
                });

                video.addEventListener('mouseout', function() {
                    this.pause();
                    this.currentTime = 0;
                });
            });
        }

        // Event listener for floating widget close button
        if (floatingWidget) {
            floatingWidget.querySelector('.kbvw-floating-widget-close').addEventListener('click', function() {
                closeFloatingWidget();
            });
        }
    });
    </script>

    {* --- CSS Styles --- *}
    <style>
    .kbvw-floating-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 320px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        z-index: 1000;
    }

    .kbvw-floating-widget-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }

    .kbvw-floating-widget-title {
        font-weight: 600;
        font-size: 14px;
        color: #333;
    }

    .kbvw-floating-widget-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #666;
        cursor: pointer;
        padding: 0 5px;
    }

    .kbvw-floating-widget-content {
        width: 100%;
    }

    .kbvw-floating-widget-video {
        width: 100%;
        border-radius: 0 0 8px 8px;
    }

    .kbvw-carousel-widget {
        margin: 20px 0;
    }

    .kbvw-carousel-widget-heading {
        text-align: center;
        margin-bottom: 18px;
        font-weight: 600;
        font-size: 1.5rem;
    }

    .kbvw-carousel-widget-inner {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        padding: 10px 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .kbvw-carousel-widget-inner::-webkit-scrollbar {
        display: none;
    }

    .kbvw-carousel-widget-item {
        flex: 0 0 300px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .kbvw-carousel-widget-thumbnail {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
    }

    .kbvw-carousel-widget-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .kbvw-carousel-widget-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.7);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .kbvw-carousel-widget-item:hover .kbvw-carousel-widget-play {
        opacity: 1;
    }

    .kbvw-carousel-widget-info {
        padding: 15px;
    }

    .kbvw-carousel-widget-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .kbvw-grid-widget {
        margin: 20px 0;
    }

    .kbvw-grid-widget-heading {
        text-align: center;
        margin-bottom: 18px;
        font-weight: 600;
        font-size: 1.5rem;
    }

    .kbvw-grid-widget-inner {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 10px 0;
    }

    .kbvw-grid-widget-item {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .kbvw-grid-widget-thumbnail {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
    }

    .kbvw-grid-widget-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .kbvw-grid-widget-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.7);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .kbvw-grid-widget-item:hover .kbvw-grid-widget-play {
        opacity: 1;
    }

    .kbvw-grid-widget-info {
        padding: 15px;
    }

    .kbvw-grid-widget-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    @media (max-width: 768px) {
        .kbvw-floating-widget {
            width: 280px;
        }

        .kbvw-carousel-widget-item {
            flex: 0 0 250px;
        }

        .kbvw-grid-widget-inner {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
    }
    </style>
{/if} 