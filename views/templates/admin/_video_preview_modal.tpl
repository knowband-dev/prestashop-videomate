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

<div id="video-preview-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="videoPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoPreviewModalLabel">{l s='Video Preview' mod='kbvideomate'}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <video id="kbvideomate-preview-video" controls style="width:100%; height:538px; background:#000;">
                        <source src="" type="video/mp4">
                        {l s='Your browser does not support the video tag.' mod='kbvideomate'}
                    </video>
                </div>
            </div>
        </div>
    </div>
</div> 