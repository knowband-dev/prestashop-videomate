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

{if isset($single_video) && $single_video}
    <div class="kbvw-single-video-container">
        <div class="kbvw-single-video-wrapper position-relative">
            <video class="kbvw-single-video" controls>
                <source src="{$single_video.0.video_url}" type="video/mp4">
                {l s='Your browser does not support the video tag.' mod='kbvideomate'}
            </video>
        </div>
    </div>
    <style>
        .kbvw-single-video-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .kbvw-single-video-wrapper {
            position: relative;
            width: 100%;
        }
        .kbvw-single-video {
            width: 100%;
            display: block;
        }
    </style>
{/if}
