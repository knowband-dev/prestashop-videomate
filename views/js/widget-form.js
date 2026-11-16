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
$(document).ready(function () {
  /*
  * Inject the Carousel/Floating/Product Video icons into their radio labels via JS
  * (kbWidgetTypeIcons, set in AdminKbVideoMateController::getWidgetTypeRadioValues()).
  * Done here instead of server-side HTML in the label because PrestaShop's back-office
  * Smarty auto-escapes HelperForm radio labels, which turned an embedded <img>/<br>
  * into literal escaped tag text on the page.
  * 03-08-2026
  */
  if (typeof kbWidgetTypeIcons !== 'undefined') {
    $.each(kbWidgetTypeIcons, function (id, icon) {
      var $label = $('label[for="' + id + '"]');
      if ($label.length && !$label.find('.kb-widget-type-img').length) {
        $label.prepend('<br>').prepend(
          $('<img>', { src: icon.src, alt: icon.alt, class: 'kb-widget-type-img' })
        );
      }
    });
  }

  /**
   * Updates the visibility of form fields based on the selected widget type.
   * - If 'carousel': shows Max Visible, Widget Heading, Carousel Autoplay Duration; hides Floating Position, Floating Remove on Close.
   * - If 'floating': shows Floating Position, Floating Remove on Close; hides Max Visible, Widget Heading, Carousel Autoplay Duration.
   *
   * Field IDs are based on the PHP form definition in AdminKbVideoMateController.
   */
  function updateWidgetFields() {
    var widgetType = $('input[name="widget_type"]:checked').val();
    var $videoField = $('select[name^="id_kbvideomate_library"]');
    var $maxVisibleGroup = $("#kbvideomate-max-visible").closest(".form-group");
    var $floatingPositionGroup = $("#kbvideomate-floating-position").closest(".form-group");
    var $floatingRemoveGroup = $('[name="floating_remove_on_close"]').closest(".form-group");
    /*
    * Bug fix: the extra .parent().closest(".form-group") hop here climbed OUT of the
    * field's own (correctly-found) outer wrapper looking for a second, higher-up
    * .form-group ancestor that doesn't exist in this form's flat field layout - it
    * resolved to an empty jQuery set, so .hide()/.show() below were silent no-ops and
    * this field's visibility was never actually controlled by widget_type at all.
    * A single .closest(".form-group") from the per-language input already reaches the
    * correct outer field wrapper (same as every other non-lang field on this form).
    * 03-08-2026
    */
    var $widgetHeadingGroup = $('input[id^="kbvideomate-widget-heading_"]').closest(".form-group");
    var $carouselAutoplayGroup = $("#kbvideomate-carousel-autoplay").closest(".form-group");
    /* Video as main image toggle - shown only for Product Video. Match name with/without table prefix.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026 
    */
    var $videoAsMainGroup = $('input[name*="video_as_main_image"]').first().closest(".form-group");

    // Always hide floating, heading, and video-as-main fields first for safety
    $floatingPositionGroup.hide();
    $floatingRemoveGroup.hide();
    $widgetHeadingGroup.hide();
    $carouselAutoplayGroup.hide();
    $videoAsMainGroup.hide();

    /*
    * Destroy previous select2 instance if exists, so switching widget_type can
    * rebuild it in the correct single-vs-multiple UI mode.
    * Bug fix: "select2-hidden-accessible" is the Select2 v4 marker class - this
    * module loads Select2 v3.x (see the 'select2-selecting' event used elsewhere
    * in this form, and entity-field.js's "select2-offscreen" check), which never
    * adds that class. The condition was therefore always false, so the existing
    * v3 instance was never destroyed before switching widget_type - only the raw
    * multiple/name attributes changed underneath it, while the visible picker
    * stayed in whatever single-vs-multiple UI shape it was first built in (e.g.
    * the default Floating single-select). Picking a video in that stale UI after
    * switching to Product/Carousel did not reliably register as the renamed
    * multi-select's real value, so nothing was actually submitted - surfacing
    * server-side as "Property KbVideoMateWidget->id_kbvideomate_library is
    * empty." `.data('select2')` is the version-agnostic way to detect an
    * existing instance (both v3 and v4 store it there).
    * 2026-08-05
    */
    if ($videoField.data('select2')) {
      $videoField.select2("destroy");
    }

    if (widgetType === "carousel") {
      $videoField.attr("multiple", "multiple");
      $videoField.attr("name", "id_kbvideomate_library[]");
      $maxVisibleGroup.show();
      $widgetHeadingGroup.show();
      $carouselAutoplayGroup.show(); // Show autoplay duration for carousel
      /*
      * Bug fix: this used to unconditionally clear the field
      * ($videoField.val(null).trigger('change')) whenever kbSelectedVideoIds was
      * undefined - which is ALWAYS true on Add, since that variable is only set
      * (via Media::addJsDef) when editing an existing widget. So every time this
      * branch ran on an Add form - including immediately after the merchant
      * switched widget_type to Carousel/Product, whether or not they had already
      * picked a video - it silently wiped the selection. HelperForm never
      * pre-selects an option on a fresh Add form, so there was never a real
      * "default selection" to guard against here; this was destroying genuine
      * user input to solve a non-problem. A single previously-picked video
      * remains valid under any widget type (all are capped at 1 anyway), so
      * nothing needs to be cleared when switching types.
      * 2026-08-05
      */
    } else if (widgetType === "floating") {
      $videoField.removeAttr("multiple");
      $videoField.attr("name", "id_kbvideomate_library");
      $maxVisibleGroup.hide();
      // Set the preselected video for floating type
      if (typeof kbSelectedVideoIds !== 'undefined' && kbSelectedVideoIds.length > 0) {
        $videoField.val(kbSelectedVideoIds[0]).trigger('change');
      }
      $floatingPositionGroup.show();
      $floatingRemoveGroup.show();
    } else if (widgetType === "product") {
      $videoField.attr("multiple", "multiple");
      $videoField.attr("name", "id_kbvideomate_library[]");
      $maxVisibleGroup.hide();
      $widgetHeadingGroup.hide();
      $carouselAutoplayGroup.hide();
      $videoAsMainGroup.show();
      // See the Carousel branch above for why the old unconditional clear-on-switch was removed.
    } else {
      $videoField.removeAttr("multiple");
      $videoField.attr("name", "id_kbvideomate_library");
      $maxVisibleGroup.hide();
      // Set the preselected video for other types
      if (typeof kbSelectedVideoIds !== 'undefined' && kbSelectedVideoIds.length > 0) {
        $videoField.val(kbSelectedVideoIds[0]).trigger('change');
      }
    }

    // Re-initialize select2
    $videoField.select2();

    // Set preselected values for edit mode
    if (typeof kbSelectedVideoIds !== 'undefined') {
      if (widgetType === 'carousel') {
        $videoField.val(kbSelectedVideoIds).trigger('change');
      } else if (kbSelectedVideoIds.length > 0) {
        $videoField.val(kbSelectedVideoIds[0]).trigger('change');
      }
    }
  }

  // Initial call
  updateWidgetFields();

  // On widget type change
  $(document).on("change", 'input[name="widget_type"]', function () {
    updateWidgetFields();
  });
}); 