/**
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
 * Free-tier build: shared "upgrade to premium" modal + interceptors, loaded on every
 * admin page this module renders (General Setting, Video Library, AI Video Library,
 * Video Widget). One shared modal + a handful of intercept helpers used to gate every
 * paid-only control across all of these pages, so there is a single place to change the
 * message/behaviour rather than near-duplicate implementations per page.
 *
 * A popup (not a red PrestaShop error banner) is the required feedback everywhere a
 * restricted action is blocked. Two layers make that true even across a full page
 * reload:
 *   1. Client-side interception below stops the action before it ever reaches the
 *      server (revert-and-show-modal for selects/radios, block-and-show-modal for
 *      buttons/toggles) - the common case, no server round trip at all.
 *   2. For a JS-bypassed submit, the server still refuses to save but redirects back
 *      with a `kbvmLocked=1` flag instead of setting a PrestaShop error - see
 *      kbvmMaybeShowUpgradeModalOnLoad() below, wired from
 *      _kbvideomate_free_notice.tpl on every page load.
 */
(function ($) {
    'use strict';

    /** Show the shared "this feature requires the premium version" modal. */
    window.kbvmShowUpgradeModal = function () {
        $('#kbvm-upgrade-modal').addClass('kbvm-upgrade-modal-visible');
        $('body').addClass('kbvm-modal-open');
    };

    function kbvmHideUpgradeModal() {
        $('#kbvm-upgrade-modal').removeClass('kbvm-upgrade-modal-visible');
        $('body').removeClass('kbvm-modal-open');
    }

    /**
     * Bind on a delegated selector, in the CAPTURE phase on `document`, so a click on a
     * gated control shows the modal instead of performing its normal action - including
     * when the feature's own handler is bound directly on the element (not delegated)
     * rather than via a jQuery delegated bubble-phase listener. Capture fires top-down
     * (document -> target) before ANY bubble-phase listener on the target itself,
     * regardless of script load order.
     */
    window.kbvmInterceptGatedControl = function (selector) {
        document.addEventListener('click', function (e) {
            var el = e.target.closest ? e.target.closest(selector) : null;
            if (!el) { return; }
            e.preventDefault();
            if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }
            window.kbvmShowUpgradeModal();
        }, true);
    };

    /** Convenience wrapper for a fixed list of element IDs. */
    window.kbvmInterceptGatedIds = function (ids) {
        window.kbvmInterceptGatedControl('#' + ids.join(', #'));
    };

    /**
     * Bind on `change` for native toggle/select controls that should stay fully
     * interactive - no `disabled`/`readonly`. The control may still visually flip
     * (browsers commit that before `change` fires; fighting it isn't worth the
     * complexity), but the modal makes clear the change won't be saved, and the real
     * enforcement is the server-side force-reset on save.
     */
    window.kbvmInterceptGatedChange = function (selector) {
        $(document).on('change', selector, function () {
            window.kbvmShowUpgradeModal();
        });
    };

    /**
     * For a <select>/radio-group whose value CAN be cleanly reverted (unlike a
     * checkbox's visual commit), revert to the last valid value and show the modal
     * instead of merely warning. Used for the Video Widget "Page" select and
     * "Widget Type" radio, where `lockedValues` identifies the premium-only choices.
     * `getValue`/`setValue` abstract over `<select>` vs radio-group so one helper
     * covers both.
     */
    window.kbvmInterceptRevertingChoice = function (selector, lockedValues, getValue, setValue) {
        var $el = $(selector);
        var lastValid = getValue($el);
        $(document).on('change', selector, function () {
            var current = getValue($el);
            if (lockedValues.indexOf(current) !== -1) {
                setValue($el, lastValid);
                window.kbvmShowUpgradeModal();
            } else {
                lastValid = current;
            }
        });
    };

    /**
     * If the server redirected back with `kbvmLocked=1` (a save was blocked
     * server-side, e.g. a JS-bypassed submit), show the modal on load instead of
     * ever rendering a PrestaShop error banner for it.
     */
    function kbvmMaybeShowUpgradeModalOnLoad() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('kbvmLocked') === '1') {
            window.kbvmShowUpgradeModal();
        }
    }

    $(document).ready(function () {
        $(document).on('click', '.kbvm-upgrade-modal-close', kbvmHideUpgradeModal);
        $(document).on('click', '.kbvm-upgrade-modal-overlay', function (e) {
            if (e.target === this) { kbvmHideUpgradeModal(); }
        });

        /**
         * readonly inputs under a .kbvm-locked-field still allow focus + a native
         * browser copy of the value - surface the same modal on click so the gate is
         * obvious rather than the field just silently refusing to accept keystrokes.
         */
        $(document).on('mousedown', 'input.kbvm-locked-field, textarea.kbvm-locked-field, select.kbvm-locked-field', function () {
            window.kbvmShowUpgradeModal();
        });

        /**
         * Global, always-on intercept for any button/link marked .kbvm-locked-trigger
         * (e.g. AI Video Library's Save) - every template can just add this class
         * instead of calling kbvmInterceptGatedControl itself.
         */
        window.kbvmInterceptGatedControl('.kbvm-locked-trigger');

        /** Same, but for native toggle/select controls (see kbvmInterceptGatedChange). */
        window.kbvmInterceptGatedChange('.kbvm-locked-toggle');

        kbvmMaybeShowUpgradeModalOnLoad();
    });
})(jQuery);
