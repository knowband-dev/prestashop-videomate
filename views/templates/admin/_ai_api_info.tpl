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

<div id="kbvideomate-ai-api-setup-panel" class="panel">
    <h3>{l s='AI API Key Setup Instructions' mod='kbvideomate'}</h3>

    <p>{l s='To use AI video generation features, you need to set up an API Key. Follow these steps for the respective API provider:' mod='kbvideomate'}</p>
    <span class="kbvm-premium-note">{l s='AI Video Generation is a premium feature - it is shown here for reference and requires the premium version to take effect.' mod='kbvideomate'}</span>

    <ul>
        <li>
            <strong>{l s='Gemini API Key Setup' mod='kbvideomate'}:</strong>
            <p>{l s='Go to' mod='kbvideomate'} <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">Gemini API Key</a> {l s='to create an API key. Paste it into the module settings in your PrestaShop Back Office.' mod='kbvideomate'}</p>
        </li>
        <li>
            <strong>{l s='ChatGPT API Key Setup' mod='kbvideomate'}:</strong>
            <p>{l s='Go to' mod='kbvideomate'} <a href="https://platform.openai.com/account/api-keys" target="_blank" rel="noopener noreferrer">ChatGPT API Key</a> {l s='to create an API key. Copy and enter it in the module settings.' mod='kbvideomate'}</p>
        </li>
    </ul>

    <h4>{l s='Billing Requirement' mod='kbvideomate'}</h4>
    <div class="alert alert-warning">
        <p><strong>{l s='Gemini:' mod='kbvideomate'}</strong> {l s='Ensure you have sufficient credits. Check your balance' mod='kbvideomate'} <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">here</a>.</p>
        <p><strong>{l s='ChatGPT:' mod='kbvideomate'}</strong> {l s='Ensure you have sufficient credits. Check your balance' mod='kbvideomate'} <a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank" rel="noopener noreferrer">here</a>.</p>
    </div>

    <div class="alert alert-info">
        <strong>{l s='Security Notice:' mod='kbvideomate'}</strong>
        {l s='Keep your API keys secure and do not share them publicly, as they are linked to your account usage and billing.' mod='kbvideomate'}
    </div>
</div>

<style>
#kbvideomate-ai-api-setup-panel {
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}
#kbvideomate-ai-api-setup-panel h3, #kbvideomate-ai-api-setup-panel h4 {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}
#kbvideomate-ai-api-setup-panel p {
    font-size: 1em;
    color: #555;
    line-height: 1.5em;
}
#kbvideomate-ai-api-setup-panel ul {
    padding-left: 20px;
    list-style-type: disc;
}
#kbvideomate-ai-api-setup-panel ul li {
    margin-bottom: 10px;
}
#kbvideomate-ai-api-setup-panel ul li strong {
    color: #333;
    font-weight: bold;
}
#kbvideomate-ai-api-setup-panel a {
    color: #007bff;
    text-decoration: underline;
}
#kbvideomate-ai-api-setup-panel a:hover {
    color: #0056b3;
    text-decoration: none;
}
</style>
