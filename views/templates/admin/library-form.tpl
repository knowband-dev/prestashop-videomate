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

{* Video Library Add Form for kbvideomate *}
<form id="kbvideomate_library_form" method="post" enctype="multipart/form-data">
    {if isset($edit_video)}
        <input type="hidden" name="id_kbvideomate_library" value="{$edit_video.id_kbvideomate_library}" />
    {/if}
    <div class="form-group">
        <label for="alias_name">{l s='Alias Name' mod='kbvideomate'}</label>
        <input type="text" name="alias_name" class="form-control" value="{if isset($edit_video)}{$edit_video.alias_name|escape:'html':'UTF-8'}{/if}" required />
    </div>
    <div class="form-group">
        <label for="video_file">{l s='Video File (MP4, max 50MB)' mod='kbvideomate'}</label>
        <input type="file" name="video_file" class="form-control" accept="video/mp4" {if !isset($edit_video)}required{/if} />
        {if isset($edit_video)}
            <small>{l s='Leave blank to keep existing video.' mod='kbvideomate'}</small>
        {/if}
    </div>
    <button type="submit" class="btn btn-success">
        <i class="icon icon-save"></i> {if isset($edit_video)}{l s='Update Video' mod='kbvideomate'}{else}{l s='Add Video' mod='kbvideomate'}{/if}
    </button>
</form> 