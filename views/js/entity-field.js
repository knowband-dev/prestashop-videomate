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
// JS for dynamic entity field in Product Video mapping (Select2 v3.x UI, manual AJAX, no select2 ajax)
$(document).ready(function () {
  var $entitySelect = $("#kb-entity-select");
  var $entityGroup = $entitySelect.closest(".form-group");
  var ajaxPages = ["product", "category", "cms", "manufacturer", "supplier"];
  var searchTimeout = null;
  var currentSearchTerm = "";
  var $pageSelect = $('select[name="page"]');
  /*
  * When page select is absent (e.g. AI form), use kbEntityDefaultPage so entity field works without Page field.
  * @modifier Himanshu Vishwakarma
  * @date 10-02-2026
  */
  function getPageValue() {
    return $pageSelect.length ? $pageSelect.val() : (typeof kbEntityDefaultPage !== "undefined" ? kbEntityDefaultPage : null);
  }
  var $entitySelectWrapper = $("#kb-entity-select").closest(
    ".form-group, .input-group, div"
  );

  // Use the token passed from the controller/template as a global variable
  function getToken() {
    // kbvideomate_token must be defined in the template
    return typeof kbvideomate_token !== "undefined" ? kbvideomate_token : "";
  }

  // Debounce function to limit AJAX calls
  function debounce(func, wait) {
    return function () {
      var context = this;
      var args = arguments;
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }

  // Attach input handler to the select2 search input inside the kb-entity-select container
  function attachEntityInputHandler() {
    // Remove any previous handlers to avoid duplicates
    $(".kb-entity-select .select2-input").off("input.kb keyup.kb");

    // Create debounced version of the search function
    var debouncedSearch = debounce(function (term) {
      currentSearchTerm = term;
      if (term.length >= 1) {
        fetchEntities(term);
      } else {
        // Reset the select2 field if input is less than 2 characters
        if ($entitySelect.hasClass("select2-offscreen")) {
          
        }
        
        $entitySelect.select2({ width: "100%" });
        attachEntityInputHandler();
      }
    }, 500); // 300ms debounce delay

    // Attach the event handlers
    $(".kb-entity-select .select2-input").on("input.kb keyup.kb", function () {
      var term = $(this).val();
      debouncedSearch(term);
    });
  }

  // Show/hide and initialize select2 for supported pages
  function updateEntityField() {
    var page = getPageValue();
    
    // Destroy select2 instance if it exists
    if ($entitySelect.hasClass("select2-offscreen")) {
      $entitySelect.select2('destroy');
    }
    
    // Clear all options and reset the field
    $entitySelect.empty();
    currentSearchTerm = "";
    
    if (ajaxPages.indexOf(page) !== -1) {
      $entitySelect.attr("multiple", "multiple");
      $entityGroup.show();
      $entitySelect.select2({
        width: "100%",
        placeholder: "Search...",
        allowClear: true,
      });
      attachEntityInputHandler(); // Attach after initializing
    } else {
      $entitySelect.removeAttr("multiple");
      $entityGroup.hide();
    }
  }

  // Fetch entities via AJAX and populate select options
  function fetchEntities(term) {
    var page = getPageValue();
    if (ajaxPages.indexOf(page) === -1) return;

    /*
    * Store currently selected options so they are preserved after replacing dropdown with new results.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026
    */
    var selectedOptions = [];
    $entitySelect.find("option:selected").each(function () {
      selectedOptions.push({
        id: $(this).val(),
        text: $(this).text(),
      });
    });

    $.ajax({
      url:
        "index.php?controller=AdminKbVideoMateAjax&token=" +
        encodeURIComponent(getToken()),
      dataType: "json",
      data: {
        q: term,
        type: page,
      },
      success: function (data) {
        if (data.items && data.items.length) {
          var selectedValues = $entitySelect.val() || [];
          var newOptionsMap = {};
          $.each(data.items, function (i, item) {
            newOptionsMap[item.id] = item.text;
          });

          /*
          * Clear previous options and display only AJAX results; re-add selected items not in results.
          * 10-02-2026
          */
          $entitySelect.empty();

          $.each(data.items, function (i, item) {
            var option = new Option(
              item.text,
              item.id,
              false,
              selectedValues.indexOf(item.id) > -1 || selectedValues.indexOf(item.id.toString()) > -1
            );
            $entitySelect.append(option);
          });

          selectedOptions.forEach(function (opt) {
            if (!newOptionsMap[opt.id] && !newOptionsMap[opt.id.toString()]) {
              var option = new Option(opt.text, opt.id, true, true);
              $entitySelect.append(option);
            }
          });

          $entitySelect.select2({
            width: "100%",
            placeholder: "Search...",
          });

          setTimeout(function() {
            $entitySelect.select2("open");
            setTimeout(function() {
              var $input = $(".kb-entity-select .select2-input");
              $input.val(currentSearchTerm);
              $input.focus();
            }, 100);
          }, 0);

          attachEntityInputHandler();
        }
      },
      error: function (xhr, status, error) {
        console.error("Search failed:", error);
      },
    });
  }

  // Initial call to set up the field
  updateEntityField();

  /*
  * Only bind page change when page select exists (Add widget form); AI form has no page field.
  * 10-02-2026
  */
  if ($pageSelect.length) {
    $(document).on("change", 'select[name="page"]', function () {
      updateEntityField();
    });
  }

  // Pre-select entities in Select2 if kbSelectedEntities is defined
  if (
    typeof kbSelectedEntities !== "undefined" &&
    Array.isArray(kbSelectedEntities) &&
    kbSelectedEntities.length > 0
  ) {
    var $entitySelect = $("#kb-entity-select");
    var selectedIds = [];
    kbSelectedEntities.forEach(function (entity) {
      selectedIds.push(entity.id);
      // Add option if not present
      if (
        $entitySelect.find('option[value="' + entity.id + '"]').length === 0
      ) {
        var option = new Option(entity.name, entity.id, true, true);
        $entitySelect.append(option);
      }
    });
    // Set the value and trigger change for Select2 to update UI
    $entitySelect.val(selectedIds).trigger("change");
  }

  if (
    typeof kbSelectedVideos !== "undefined" &&
    Array.isArray(kbSelectedVideos) &&
    kbSelectedVideos.length > 0
  ) {
    var $videoSelect = $("#kb_kbvideowise_library");
    var selectedIds = [];
    kbSelectedVideos.forEach(function (video) {
      selectedIds.push(video.id);
      // Add option if not present
      if ($videoSelect.find('option[value="' + video.id + '"]').length === 0) {
        var option = new Option(video.name, video.id, true, true);
        $videoSelect.append(option);
      }
    });
    // Set the value and trigger change for Select2 to update UI
    $videoSelect.val(selectedIds).trigger("change");
  }

  /*
  * toggleEntityField only needed when page select exists (Add widget form); AI form has no page field.
  * @modifier Himanshu Vishwakarma
  * @date 10-02-2026
  */
  if ($pageSelect.length) {
    function toggleEntityField() {
      if ($pageSelect.val() === "home") {
        $entitySelectWrapper.hide();
      } else {
        $entitySelectWrapper.show();
      }
    }
    $pageSelect.on("change", toggleEntityField);
    toggleEntityField(); // Initial call on page load
  }
}); 