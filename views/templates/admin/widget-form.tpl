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

{* Widget Add/Edit Form for kbvideomate *}
<form id="kbvideomate_widget_form" method="post">
    {if isset($edit_widget)}
        <input type="hidden" name="id_kbvideomate_widget" value="{$edit_widget.id_kbvideomate_widget}" />
    {/if}
    <div class="form-group">
        <label for="widget_name">{l s='Widget Name' mod='kbvideomate'}</label>
        <input type="text" name="widget_name" class="form-control" value="{if isset($edit_widget)}{$edit_widget.widget_name|escape:'html':'UTF-8'}{/if}" required />
    </div>
    <div class="form-group">
        <label>{l s='Widget Type' mod='kbvideomate'}</label>
        {include file="@kbvideomate/views/templates/admin/widget-type-radio.tpl" widget_type=$widget_type kbvideomate_admin_img_path=$kbvideomate_admin_img_path}
    </div>
    <div class="form-group">
        <label for="widget_heading">{l s='Widget Heading' mod='kbvideomate'}</label>
        {foreach from=$languages item=language}
            <div class="translatable-field lang-{$language.id_lang}" {if !$language.is_default}style="display:none"{/if}>
                <input type="text" name="widget_heading_{$language.id_lang}" class="form-control" value="{if isset($edit_widget) && isset($edit_widget.widget_heading[$language.id_lang])}{$edit_widget.widget_heading[$language.id_lang]|escape:'html':'UTF-8'}{/if}" />
                <span class="lang-label">{$language.iso_code}</span>
            </div>
        {/foreach}
    </div>
    <div class="form-group" id="kb-max-visible-group" style="display:none;">
        <label for="kb-max-visible">{l s='Max Visible Videos' mod='kbvideomate'}</label>
        <input type="text" id="kb-max-visible" name="max_visible" class="form-control" value="{if isset($edit_widget)}{$edit_widget.max_visible|escape:'html':'UTF-8'}{else}3{/if}" />
    </div>
    <div class="form-group" id="kb-carousel-autoplay-duration-group" style="display:none;">
        <label for="kb-carousel-autoplay-duration">{l s='Carousel Autoplay Duration (seconds)' mod='kbvideomate'}</label>
        <input type="text" id="kb-carousel-autoplay-duration" name="carousel_autoplay_duration" class="form-control" value="{if isset($edit_widget)}{$edit_widget.carousel_autoplay_duration|escape:'html':'UTF-8'}{/if}" />
    </div>
    <div class="form-group">
        <label for="video_ids">{l s='Select Videos' mod='kbvideomate'}</label>
        <select name="video_ids[]" class="form-control kb_kbvideomate_library select2" multiple required>
            {foreach from=$video_library item=video}
                <option value="{$video.id_kbvideomate_library}" {if isset($selected_video_ids) && in_array($video.id_kbvideomate_library, $selected_video_ids)}selected{/if}>{$video.alias_name|escape:'html':'UTF-8'}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="entity_type">{l s='Map To' mod='kbvideomate'}</label>
        <select name="entity_type" class="form-control" required>
            <option value="product" {if $entity_type == 'product'}selected{/if}>{l s='Product' mod='kbvideomate'}</option>
            <option value="category" {if $entity_type == 'category'}selected{/if}>{l s='Category' mod='kbvideomate'}</option>
            <option value="home" {if $entity_type == 'home'}selected{/if}>{l s='Home Page' mod='kbvideomate'}</option>
            <option value="bestseller" {if $entity_type == 'bestseller'}selected{/if}>{l s='Best Seller Page' mod='kbvideomate'}</option>
            <option value="newproduct" {if $entity_type == 'newproduct'}selected{/if}>{l s='New Product Page' mod='kbvideomate'}</option>
        </select>
    </div>
    <div id="kbvideomate_entity_mapping_container">
        {if isset($entity_mapping_tpl)}
            {include file=$entity_mapping_tpl entities=$entities selected_entity_id=$selected_entity_id}
        {/if}
    </div>
    <div class="form-group">
        <label for="active">{l s='Status' mod='kbvideomate'}</label>
        <select name="active" class="form-control">
            <option value="1" {if isset($edit_widget) && $edit_widget.active}selected{/if}>{l s='Enabled' mod='kbvideomate'}</option>
            <option value="0" {if isset($edit_widget) && !$edit_widget.active}selected{/if}>{l s='Disabled' mod='kbvideomate'}</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">
        <i class="icon icon-save"></i> {if isset($edit_widget)}{l s='Update Widget' mod='kbvideomate'}{else}{l s='Add Widget' mod='kbvideomate'}{/if}
    </button>
</form> 