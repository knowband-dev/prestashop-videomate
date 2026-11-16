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
*
* Free-tier build: banner + shared upgrade modal, fetched at the top of every admin
* controller's list/form output (see KbVideoMate::renderFreeTierNotice()). Only one of
* list/form renders per request, so this is never injected twice on the same page.
*
* Assigned vars (see renderFreeTierNotice()):
*   kbvm_upgrade_url (string)
*}
<div class="kbvm-free-banner">
    {l s='You are using the FREE version of VideoMate. Some fields and options are shown for reference only and require the premium version to take effect.' mod='kbvideomate'}
    <a href="{$kbvm_upgrade_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">{l s='Upgrade to Premium' mod='kbvideomate'}</a>
</div>
<div id="kbvm-upgrade-modal" class="kbvm-upgrade-modal-overlay">
    <div class="kbvm-upgrade-modal-box">
        <button type="button" class="kbvm-upgrade-modal-close" aria-label="{l s='Close' mod='kbvideomate'}">&times;</button>
        <h3>{l s='This is a Premium Feature' mod='kbvideomate'}</h3>
        <p>{l s='This feature is only available in the premium version of VideoMate. Upgrade to unlock it.' mod='kbvideomate'}</p>
        <a href="{$kbvm_upgrade_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener" class="btn btn-primary kbvm-upgrade-modal-cta">
            {l s='Upgrade to Premium' mod='kbvideomate'}
        </a>
    </div>
</div>
