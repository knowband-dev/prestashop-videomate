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

{* Entity mapping select for widget configuration *}
<div class="form-group">
    <label for="kbvideomate_entity_select">{l s='Map to Entity' mod='kbvideomate'}</label>
    <select id="kbvideomate_entity_select" name="entity_id" class="form-control kb-entity-select select2">
        {foreach from=$entities item=entity}
            <option value="{$entity.id}" {if $entity.id == $selected_entity_id}selected{/if}>{$entity.name|escape:'html':'UTF-8'}</option>
        {/foreach}
    </select>
</div> 