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

{* Video Library List Table for kbvideomate *}
<div class="panel">
    <h3><i class="icon icon-film"></i> {l s='Video Library' mod='kbvideomate'}</h3>
    <div class="table-responsive">
        <table class="table table-bordered kbvideomate-library-table">
            <thead>
                <tr>
                    <th>{l s='ID' mod='kbvideomate'}</th>
                    <th>{l s='Alias Name' mod='kbvideomate'}</th>
                    <th>{l s='Video' mod='kbvideomate'}</th>
                    <th>{l s='Actions' mod='kbvideomate'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$library_list item=video}
                    <tr>
                        <td>{$video.id_kbvideomate_library}</td>
                        <td>{$video.alias_name|escape:'html':'UTF-8'}</td>
                        <td>
                            <button class="btn btn-default video-preview-btn" data-video-url="{$video.video_url}">
                                <i class="icon icon-play"></i> {l s='Preview' mod='kbvideomate'}
                            </button>
                        </td>
                        <td>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_video_id" value="{$video.id_kbvideomate_library}" />
                                <button type="submit" class="btn btn-danger" onclick="return confirm('{l s='Are you sure you want to delete this video?' mod='kbvideomate'}');">
                                    <i class="icon icon-trash"></i> {l s='Delete' mod='kbvideomate'}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div> 