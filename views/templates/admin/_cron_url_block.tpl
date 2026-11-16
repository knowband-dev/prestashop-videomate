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

<div class="panel kb-cron-section">
    <div class="panel-heading"><i class="icon-external-link"></i> {$section_title|escape:'html':'UTF-8'}</div>
    <div class="panel-body">
        <div class="kb-cron-url-wrapper">
            <span class="kb-cron-url">{$cron_url|escape:'html':'UTF-8'}</span>
            <button type="button" class="kb-cron-copy-btn" data-cron-url="{$cron_url|escape:'html':'UTF-8'}"><i class="icon-copy"></i> {l s='Copy' mod='kbvideomate'}</button>
        </div>
        <p class="kb-cron-help">{$cron_help_text|escape:'html':'UTF-8'}</p>
        <span class="kbvm-premium-note">{l s='AI Video Generation is a premium feature - this URL has no effect on the free version.' mod='kbvideomate'}</span>
        <div class="kb-cron-actions">
            <button type="button" class="btn btn-primary" disabled="disabled"><i class="icon-external-link"></i> {$cron_open_btn|escape:'html':'UTF-8'}</button>
        </div>
    </div>
</div>
