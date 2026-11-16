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

<a href="#{$href|escape:'htmlall':'UTF-8'}" title="{$action|escape:'htmlall':'UTF-8'}" class="edit {$href|escape:'htmlall':'UTF-8'}">
    <i class="icon-{$icon|escape:'htmlall':'UTF-8'}"></i> {$action|escape:'htmlall':'UTF-8'}
</a>
<div style="display:none;" id="{$href|escape:'htmlall':'UTF-8'}">{$text|escape:'html':'UTF-8'|nl2br}</div>
