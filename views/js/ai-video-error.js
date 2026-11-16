/**
 * [2020] PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2020 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

(function waitForJQuery() {
    if (typeof window.jQuery === 'undefined') {
        setTimeout(waitForJQuery, 50);
        return;
    }
    var $ = window.jQuery;
    $(document).ready(function() {
        var $links = $('a[class*="kbvideomate-error-"]');
        if (typeof $.fn.fancybox === 'function') {
            $links.fancybox({ type: 'inline', padding: 10 });
        } else {
            $links.on('click', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var targetId = href ? href.replace(/^#/, '') : '';
                var $content = targetId ? $('#' + targetId) : null;
                if (!$content || !$content.length) return;
                var $modal = $('#kbvideomate-error-modal');
                if (!$modal.length) {
                    $modal = $('<div id="kbvideomate-error-modal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">Error Details</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"></div></div></div></div>');
                    $('body').append($modal);
                }
                $modal.find('.modal-body').html($content.html());
                $modal.modal('show');
            });
        }
    });
})();
