/*
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
 */

/*
 * Video selection behavior for KbVideoMate module
 * Handles multiple video selection for carousel widget type
 */
$(document).ready(function () {
  // Initialize Select2 for video selection
  $(".kb-video-select").select2({
    placeholder: "Select video(s)",
    allowClear: true,
    width: "100%",
  });

  // Handle widget type change
  $('input[name="widget_type"]').on("change", function () {
    var widgetType = $(this).val();
    var $videoSelect = $(".kb-video-select");

    if (widgetType === "carousel") {
      // For carousel, allow multiple selection
      $videoSelect.prop("multiple", true);
      $videoSelect.select2("destroy").select2({
        placeholder: "Select multiple videos for carousel",
        allowClear: true,
        width: "100%",
      });

      // Show max visible field
      $("#kb-max-visible").closest(".form-group").show();
    } else {
      // For single/floating, only allow single selection
      $videoSelect.prop("multiple", false);
      $videoSelect.select2("destroy").select2({
        placeholder: "Select a video",
        allowClear: true,
        width: "100%",
      });

      // Hide max visible field
      $("#kb-max-visible").closest(".form-group").hide();
    }
  });

  // Trigger change on page load to set initial state
  $('input[name="widget_type"]:checked').trigger("change");
}); 