{*
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
*}

{* Container with data attr for JS to set video as main image on load. | 10-02-2026 *}
<div id="kb-product-video-thumbs"{if isset($video_as_main_image) && $video_as_main_image} data-video-as-main-image="1"{/if}>
{foreach from=$videos item=video name=videos}
    <div class="thumb-container js-thumb-container kb-video-thumb">
        {if $video.video_url|strpos:'youtube.com' !== false || $video.video_url|strpos:'youtu.be' !== false}
            {* Extract YouTube video ID *}
            {assign var="video_url" value=$video.video_url}
            {if $video_url|strpos:'youtube.com/watch?v=' !== false}
                {assign var="video_id" value=$video_url|regex_replace:"/.*v=([^&]+).*/":"$1"}
            {elseif $video_url|strpos:'youtube.com/embed/' !== false}
                {assign var="video_id" value=$video_url|regex_replace:"/.*embed\/([^?]+).*/":"$1"}
            {elseif $video_url|strpos:'youtu.be/' !== false}
                {assign var="video_id" value=$video_url|regex_replace:"/.*youtu.be\/([^?]+).*/":"$1"}
            {/if}
            {if $video_id}
                <img
                    class="thumb js-thumb kb-video-thumb-img"
                    src="https://img.youtube.com/vi/{$video_id|escape:'htmlall':'UTF-8'}/mqdefault.jpg"
                    data-video-type="youtube"
                    data-video-id="{$video_id|escape:'htmlall':'UTF-8'}"
                    data-video-url="{$video.video_url|escape:'htmlall':'UTF-8'}"
                    alt="{$product->name|escape:'htmlall':'UTF-8'} - {l s='Video' mod='kbvideomate'} {$smarty.foreach.videos.iteration}"
                    title="{$product->name|escape:'htmlall':'UTF-8'} - {l s='Video' mod='kbvideomate'} {$smarty.foreach.videos.iteration}"
                    width="100"
                    height="100"
                    itemprop="image"
                >
            {/if}
        {elseif $video.video_url|strpos:'vimeo.com' !== false}
            {* Extract Vimeo video ID *}
            {assign var="video_url" value=$video.video_url}
            {assign var="video_id" value=$video_url|regex_replace:"/.*vimeo.com\/([0-9]+).*/":"$1"}
            {if $video_id}
                <img
                    class="thumb js-thumb kb-video-thumb-img"
                    src="{$module_dir}views/img/video-placeholder.jpg"
                    data-video-type="vimeo"
                    data-video-id="{$video_id|escape:'htmlall':'UTF-8'}"
                    data-video-url="{$video.video_url|escape:'htmlall':'UTF-8'}"
                    alt="{$product->name|escape:'htmlall':'UTF-8'} - {l s='Video' mod='kbvideomate'} {$smarty.foreach.videos.iteration}"
                    title="{$product->name|escape:'htmlall':'UTF-8'} - {l s='Video' mod='kbvideomate'} {$smarty.foreach.videos.iteration}"
                    width="100"
                    height="100"
                    itemprop="image"
                >
            {/if}
        {else}
            <video
                class="thumb js-thumb kb-video-thumb-img"
                width="100"
                height="100"
                preload="metadata"
                muted
                playsinline
                data-video-type="html5"
                data-video-url="{$video.video_url|escape:'htmlall':'UTF-8'}"
            >
                <source src="{$video.video_url|escape:'htmlall':'UTF-8'}" type="video/mp4">
            </video>
        {/if}
    </div>
{/foreach}
</div>