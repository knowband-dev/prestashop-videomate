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

{* Widget List Table for kbvideomate *}
<div class="panel">
    <h3><i class="icon icon-puzzle-piece"></i> {l s='Video Widgets' mod='kbvideomate'}</h3>
    <div class="table-responsive">
        <table class="table table-bordered kbvideomate-widget-table">
            <thead>
                <tr>
                    <th>{l s='ID' mod='kbvideomate'}</th>
                    <th>{l s='Widget Name' mod='kbvideomate'}</th>
                    <th>{l s='Type' mod='kbvideomate'}</th>
                    <th>{l s='Mapped To' mod='kbvideomate'}</th>
                    <th>{l s='Status' mod='kbvideomate'}</th>
                    <th>{l s='Actions' mod='kbvideomate'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$widget_list item=widget}
                    <tr>
                        <td>{$widget.id_kbvideomate_widget}</td>
                        <td>{$widget.widget_name|escape:'html':'UTF-8'}</td>
                        <td>{l s=$widget.widget_type mod='kbvideomate'}</td>
                        <td>{$widget.mapped_to|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $widget.active}
                                <span class="label label-success">{l s='Enabled' mod='kbvideomate'}</span>
                            {else}
                                <span class="label label-danger">{l s='Disabled' mod='kbvideomate'}</span>
                            {/if}
                        </td>
                        <td>
                            <a href="{$widget.edit_url}" class="btn btn-default">
                                <i class="icon icon-edit"></i> {l s='Edit' mod='kbvideomate'}
                            </a>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_widget_id" value="{$widget.id_kbvideomate_widget}" />
                                <button type="submit" class="btn btn-danger" onclick="return confirm('{l s='Are you sure you want to delete this widget?' mod='kbvideomate'}');">
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