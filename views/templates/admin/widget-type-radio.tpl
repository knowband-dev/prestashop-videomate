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

<div class="kb-widget-type-radio">
    <input type="radio" id="kbvideomate_widget_type_carousel" name="widget_type" value="carousel" {if $widget_type == 'carousel'}checked{/if}>
    <label for="kbvideomate_widget_type_carousel">
        <img src="{$kbvideomate_admin_img_path}carousel.png" alt="Carousel" />
        {l s='Carousel' mod='kbvideomate'}
    </label>
    <input type="radio" id="kbvideomate_widget_type_floating" name="widget_type" value="floating" {if $widget_type == 'floating'}checked{/if}>
    <label for="kbvideomate_widget_type_floating">
        <img src="{$kbvideomate_admin_img_path}floating.png" alt="Floating" />
        {l s='Floating' mod='kbvideomate'}
    </label>
</div> 