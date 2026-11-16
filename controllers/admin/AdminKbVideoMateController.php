<?php
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
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'kbvideomate/classes/KbVideoMateWidget.php';
require_once _PS_MODULE_DIR_.'kbvideomate/classes/KbVideoMateLibrary.php';

/**
 * Admin controller for the Video Widget tab
 * Allows CRUD for video widget mappings with dynamic field visibility
 */
class AdminKbVideoMateController extends ModuleAdminController
{
    /**
     * Normalized form values for save; used instead of $_POST/GET/REQUEST (PrestaShop Addons compliance).
     * Set in postProcess(), applied in processUpdate() / processAdd().
     * 25-02-2025
     * @var string|null
     */
    protected $submitted_id_kbvideomate_library;

    /** @var string|null */
    protected $submitted_entity_ids;

    /** @var string|null */
    protected $submitted_widget_type;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'kbvideomate';
        $this->className = 'KbVideoMateWidget';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = false;
        $this->deleted = false;
        $this->context = Context::getContext();
        parent::__construct();
        $module = Module::getInstanceByName('kbvideomate');
        if ($module instanceof Module) {
            $this->module = $module;
        } else {
            // Optional: throw error or handle gracefully
            throw new PrestaShopException('Module kbvideomate is not installed or could not be loaded.');
        }

        // List fields for the widget mapping
        $this->fields_list = [
            'widget_type' => [
                'title' => $this->module->l('Widget Type', 'AdminKbVideoMateController'),
                'align' => 'center',
                'callback' => 'getWidgetTypeLabel',
            ],
            'page' => [
                'title' => $this->module->l('Page', 'AdminKbVideoMateController'),
                'align' => 'center',
                'callback' => 'getPageLabel',
            ],
            'active' => [
                'title' => $this->module->l('Status', 'AdminKbVideoMateController'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
            ],
            'date_add' => [
                'title' => $this->module->l('Date Added', 'AdminKbVideoMateController'),
                'align' => 'center',
                'type' => 'datetime',
            ],
            'date_upd' => [
                'title' => $this->module->l('Date Updated', 'AdminKbVideoMateController'),
                'align' => 'center',
                'type' => 'datetime',
            ],
        ];
        $this->_select = 'a.*';
        $this->bulk_actions = [];
    }

    /**
     * Callback for translated widget type label in listing
     */
    public function getWidgetTypeLabel($value, $row)
    {
        $labels = [
            'carousel' => $this->module->l('Carousel', 'AdminKbVideoMateController'),
            'floating' => $this->module->l('Floating', 'AdminKbVideoMateController'),
            'product' => $this->module->l('Product Video', 'AdminKbVideoMateController'),
        ];
        return isset($labels[$value]) ? $labels[$value] : $value;
    }

    protected function getWidgetTypeRadioValues()
    {
        $imgBase = '../modules/kbvideomate/views/img/';
        $options = [
            ['id' => 'widget_carousel', 'value' => 'carousel', 'icon' => 'carousel.gif', 'alt' => 'Carousel', 'label_key' => 'Carousel'],
            ['id' => 'widget_floating', 'value' => 'floating', 'icon' => 'Icon.png', 'alt' => 'Floating', 'label_key' => 'Floating'],
            ['id' => 'widget_product', 'value' => 'product', 'icon' => 'Icon.png', 'alt' => 'Product Video', 'label_key' => 'Product Video'],
        ];
        $values = [];
        $icons = [];
        foreach ($options as $opt) {
            /*
            * HTML-escaping fix: PrestaShop's back-office Smarty auto-escapes
            * {$value.label} in HelperForm's core radio template, so a pre-rendered
            * <img>/<br> string here showed up as literal escaped tag text instead of
            * an image. Keep 'label' as plain text and inject the icon via JS instead
            * (see kbWidgetTypeIcons below, consumed by widget-form.js) - the
            * input/value/checked mechanics are untouched.
            */
            $values[] = [
                'id' => $opt['id'],
                'value' => $opt['value'],
                'label' => $this->module->l($opt['label_key'], 'AdminKbVideoMateController'),
            ];
            $icons[$opt['id']] = [
                'src' => $imgBase . $opt['icon'],
                'alt' => $opt['alt'],
            ];
        }
        Media::addJsDef(['kbWidgetTypeIcons' => $icons]);
        return $values;
    }

    /**
     * Callback for translated page label in listing
     */
    public function getPageLabel($value, $row)
    {
        $labels = [
            'product' => $this->module->l('Product', 'AdminKbVideoMateController'),
            'category' => $this->module->l('Category', 'AdminKbVideoMateController'),
            'home' => $this->module->l('Home', 'AdminKbVideoMateController'),
            'best_seller' => $this->module->l('Best Seller', 'AdminKbVideoMateController'),
            'new_product' => $this->module->l('New Product', 'AdminKbVideoMateController'),
        ];
        return isset($labels[$value]) ? $labels[$value] : $value;
    }

    /**
     * Free-tier build: shared banner + upgrade modal at the top of the listing.
     */
    public function renderList()
    {
        /*
        * Free-tier build: shop-wide widget-mapping cap (KbVideoMate::FREE_WIDGET_CAP,
        * 5 total) - surface it as a native PrestaShop header warning on the list page
        * whenever it's reached (also covers the redirect-back-to-list after a blocked
        * Add attempt in renderForm()).
        */
        if ($this->module->kbvmHasReachedWidgetCap()) {
            $this->warnings[] = $this->module->l('No more form submissions are being accepted - the free version limit has been reached. Upgrade to the premium version for more submissions.', 'AdminKbVideoMateController');
        }

        return $this->module->renderFreeTierNotice() . parent::renderList();
    }

    /**
     * Render the form for creating/editing a widget mapping
     * Implements dynamic field visibility using JS
     */
    public function renderForm()
    {
        /*
        * Free-tier build: shop-wide widget-mapping cap (KbVideoMate::FREE_WIDGET_CAP,
        * 5 total, any widget_type combined) - block the Add form from ever rendering
        * once reached (editing an existing widget never creates a new row, so it's
        * unaffected). Redirect back to the list with kbvmLocked=1 so the shared JS
        * engine shows the upgrade modal on load, same pattern used for every other
        * server-side reject in this module.
        */
        if (!Tools::getValue('id_kbvideomate') && $this->module->kbvmHasReachedWidgetCap()) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&kbvmLocked=1');
        }

        // Free-tier build: Carousel is premium - default the Add form to Floating instead.
        if (!Tools::getValue('id_kbvideomate')) {
            $this->fields_value = $this->fields_value ?? [];
            $this->fields_value['widget_type'] = 'floating';
        }

        /*
        * Free-tier build: Popup Position Left is premium - always open the control on
        * Right (Add and Edit), even if an older row still stores left in the DB.
        */
        $this->fields_value = $this->fields_value ?? [];
        $this->fields_value['floating_position'] = 'right';

        // Fetch all products for select2
        $products = Product::getProducts($this->context->language->id, 0, 0, 'name', 'ASC');
        // Fetch only enabled videos from library for select2
        $videos = KbVideoMateLibrary::getAllForSelect(true);
        
        // If editing, ensure the currently selected video is included even if disabled
        if ($id_kbvideomate = Tools::getValue('id_kbvideomate')) {
            // FIX: Use the correct widget model class (KbVideoMateWidget) instead of non-existent KbVideoMateProduct
            // Reference: Patch for ClassNotFoundError, see chat and requirements
            require_once _PS_MODULE_DIR_.'kbvideomate/classes/KbVideoMateWidget.php';
            $obj = new KbVideoMateWidget($id_kbvideomate);
            if (Validate::isLoadedObject($obj)) {
                // --- ENTITY PRESELECTION ---
                if (!empty($obj->entity_ids)) {
                    $entityIds = explode(',', $obj->entity_ids);
                    $selectedEntities = [];
                    switch ($obj->page) {
                        case 'product':
                            foreach ($entityIds as $id) {
                                $product = new Product((int)$id, false, $this->context->language->id);
                                $selectedEntities[] = ['id' => $id, 'name' => $product->name];
                            }
                            break;
                        case 'category':
                            foreach ($entityIds as $id) {
                                $category = new Category((int)$id, $this->context->language->id);
                                $selectedEntities[] = ['id' => $id, 'name' => $category->name];
                            }
                            break;
                        case 'manufacturer':
                            foreach ($entityIds as $id) {
                                $manufacturer = new Manufacturer((int)$id, $this->context->language->id);
                                $selectedEntities[] = ['id' => $id, 'name' => $manufacturer->name];
                            }
                            break;
                        case 'supplier':
                            foreach ($entityIds as $id) {
                                $supplier = new Supplier((int)$id, $this->context->language->id);
                                $selectedEntities[] = ['id' => $id, 'name' => $supplier->name];
                            }
                            break;
                        case 'cms':
                            foreach ($entityIds as $id) {
                                $cms = new CMS((int)$id, $this->context->language->id);
                                $selectedEntities[] = ['id' => $id, 'name' => $cms->meta_title];
                            }
                            break;
                    }
                    if (!empty($selectedEntities)) {
                        Media::addJsDef(['kbSelectedEntities' => $selectedEntities]);
                    }
                }
                // --- VIDEO PRESELECTION ---
                if ($obj->widget_type === 'carousel' && !empty($obj->id_kbvideomate_library)) {
                    $selectedVideoIds = explode(',', $obj->id_kbvideomate_library);
                    $selectedVideos = [];
                    foreach ($selectedVideoIds as $id) {
                        foreach ($videos as $video) {
                            if ($video['id_kbvideomate_library'] == $id) {
                                $selectedVideos[] = ['id' => $id, 'name' => $video['alias_name']];
                                break;
                            }
                        }
                    }
                    if (!empty($selectedVideos)) {
                        Media::addJsDef(['kbSelectedVideos' => $selectedVideos]);
                    }
                    Media::addJsDef(['kbSelectedVideoIds' => $selectedVideoIds]);
                } else {
                    // For floating widget type, ensure the saved video is selected
                    if (!empty($obj->id_kbvideomate_library)) {
                        $videoId = $obj->id_kbvideomate_library;
                        // Find the video in the videos array to get its name
                        foreach ($videos as $video) {
                            if ($video['id_kbvideomate_library'] == $videoId) {
                                Media::addJsDef([
                                    'kbSelectedVideoIds' => [$videoId],
                                    'kbSelectedVideos' => [['id' => $videoId, 'name' => $video['alias_name']]]
                                ]);
                                break;
                            }
                        }
                    }
                }
            }
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Product Video', 'AdminKbVideoMateController'),
                'icon' => 'icon-video-camera'
            ],
            'input' => [
                // Page field
                [
                    'type' => 'select',
                    'label' => $this->module->l('Page', 'AdminKbVideoMateController'),
                    'name' => 'page',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 'product', 'name' => $this->module->l('Product', 'AdminKbVideoMateController')],
                            ['id' => 'category', 'name' => $this->module->l('Category', 'AdminKbVideoMateController')],
                            ['id' => 'best_seller', 'name' => $this->module->l('Best Seller', 'AdminKbVideoMateController')],
                            ['id' => 'new_product', 'name' => $this->module->l('New Product', 'AdminKbVideoMateController')],
                            ['id' => 'home', 'name' => $this->module->l('Home Page', 'AdminKbVideoMateController')],
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'class' => 'select2',
                    // Free-tier build: only "Product" is available in Free - the other
                    // 4 options stay for upsell visibility. Selecting one reverts back
                    // to the last valid choice and shows the upgrade modal instead of
                    // saving - see the inline intercept script below (kbvm-page select
                    // has no per-option class hook, so it's gated by name/value in JS,
                    // not by a CSS class). Server-side reject in processAdd()/
                    // processUpdate() is the defense-in-depth backstop.
                    'hint' => $this->module->l('Select the page type where this widget will be displayed. Category, Best Seller, New Product and Home are premium features.', 'AdminKbVideoMateController')
                ],
                // Dynamic sub-entity field (hidden by default, shown by JS)
                [
                    'type' => 'select',
                    'label' => $this->module->l('Select Entity', 'AdminKbVideoMateController'),
                    'name' => 'entity_ids[]',
                    'required' => true,
                    'options' => [
                        'query' => [], // Will be populated by JS
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'id' => 'kb-entity-select', // Add unique id for JS
                    'class' => 'select2 kb-entity-select',
                    'multiple' => false, // JS will set as needed
                    'style' => 'display:none;',
                    'hint' => $this->module->l('Select the specific product, category, or entity to map this widget to.', 'AdminKbVideoMateController')
                ],
                // Video field: will be single or multiselect based on widget_type (handled by JS)
                [
                    'type' => 'select',
                    'label' => $this->module->l('Video (from Library)', 'AdminKbVideoMateController'),
                    'name' => 'id_kbvideomate_library[]',
                    'required' => true,
                    'options' => [
                        'query' => $videos,
                        'id' => 'id_kbvideomate_library',
                        'name' => 'alias_name'
                    ],
                    'class' => 'select2 kb_kbvideomate_library',
                    'multiple' => false, // Will be set to true by JS if carousel is selected
                    'placeholder' => $this->module->l('Select a video', 'AdminKbVideoMateController'),
                    'hint' => $this->module->l('Choose one or more videos from the library to display in this widget.', 'AdminKbVideoMateController')
                ],
                /* Widget type selection; labels rendered from template, no HTML in controller. | 10-02-2026 */
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Widget Type', 'AdminKbVideoMateController'),
                    'name' => 'widget_type',
                    'required' => true,
                    'values' => $this->getWidgetTypeRadioValues(),
                    'desc' => $this->module->l('Select how the video(s) will be displayed on the product page.', 'AdminKbVideoMateController'),
                    // Free-tier build: Carousel is premium - stays selectable for
                    // upsell (see getWidgetTypeRadioValues()), but choosing it reverts
                    // back to the last valid widget type and shows the upgrade modal -
                    // see the inline intercept script below. Server-side reject in
                    // processAdd()/processUpdate() is the defense-in-depth backstop.
                    'class' => 'kb-widget-type-radio',
                    'hint' => $this->module->l('Carousel displays multiple videos in a slider (premium feature). Floating shows a popup video on the page.', 'AdminKbVideoMateController')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Set video as main image', 'AdminKbVideoMateController'),
                    'name' => 'video_as_main_image',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'video_as_main_image_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminKbVideoMateController')
                        ],
                        [
                            'id' => 'video_as_main_image_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminKbVideoMateController')
                        ]
                    ],
                    'desc' => $this->module->l('When enabled, the video will be displayed as the main image in the product image gallery on the product page.', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-video-as-main-image',
                    'style' => 'display:none;',
                    'hint' => $this->module->l('Only applies to Product Video widget type.', 'AdminKbVideoMateController')
                ],
                // Max visible field for carousel (hidden by default, shown by JS)
                [
                    'type' => 'text',
                    'label' => $this->module->l('Max Visible Videos (Carousel)', 'AdminKbVideoMateController'),
                    'name' => 'max_visible',
                    'required' => true,
                    'class' => 'fixed-width-sm',
                    'desc' => $this->module->l('Maximum number of videos visible in the carousel.', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-max-visible',
                    'hint' => $this->module->l('Set the maximum number of videos to show in the carousel widget.', 'AdminKbVideoMateController')
                ],
                // Widget heading for carousel
                [
                    'type' => 'text',
                    'label' => $this->module->l('Widget Heading', 'AdminKbVideoMateController'),
                    'name' => 'widget_heading',
                    'lang' => true,
                    'required' => false,
                    'desc' => $this->module->l('Heading to display above the carousel widget.', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-widget-heading',
                    'style' => 'display:none;',
                    'hint' => $this->module->l('This heading will appear above the carousel widget. Leave blank for no heading.', 'AdminKbVideoMateController')
                ],
                // Autoplay duration for carousel (hidden by default, shown by JS)
                [
                    'type' => 'text',
                    'label' => $this->module->l('Autoplay Duration (seconds)', 'AdminKbVideoMateController'),
                    'name' => 'carousel_autoplay_duration',
                    'required' => true,
                    'class' => 'fixed-width-sm',
                    'desc' => $this->module->l('Number of seconds to play each video before automatically switching to the next (carousel only).', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-carousel-autoplay',
                    'style' => 'display:none;',
                    'hint' => $this->module->l('Set how long each video plays before the carousel moves to the next one. Only applies to carousel widgets.', 'AdminKbVideoMateController')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Active', 'AdminKbVideoMateController'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminKbVideoMateController')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminKbVideoMateController')
                        ]
                    ],
                    'hint' => $this->module->l('If disabled, this widget will not be shown on the front office.', 'AdminKbVideoMateController')
                ],
                // Floating widget: position (left/right)
                [
                    'type' => 'select',
                    'label' => $this->module->l('Popup Position', 'AdminKbVideoMateController'),
                    'name' => 'floating_position',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 'right', 'name' => $this->module->l('Right', 'AdminKbVideoMateController')],
                            ['id' => 'left', 'name' => $this->module->l('Left', 'AdminKbVideoMateController')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    // Free-tier build: only Right is available in Free - Left stays
                    // selectable for upsell. Choosing Left reverts to Right and shows
                    // the upgrade modal - see the inline intercept script below.
                    // applySubmittedValuesToWidget() always persists 'right'.
                    'class' => 'select2',
                    'desc' => $this->module->l('Show the floating popup on the right side. Left position is a premium feature.', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-floating-position',
                    'style' => 'display:none;',
                    'hint' => $this->module->l('Choose where the floating video popup will appear on the page. Left is a premium feature.', 'AdminKbVideoMateController')
                ],
                // Floating widget: remove on close
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Remove video from page when closed?', 'AdminKbVideoMateController'),
                    'name' => 'floating_remove_on_close',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'remove_on_close_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminKbVideoMateController')
                        ],
                        [
                            'id' => 'remove_on_close_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminKbVideoMateController')
                        ]
                    ],
                    'desc' => $this->module->l('If enabled, the video popup will be removed from the page when closed.', 'AdminKbVideoMateController'),
                    'id' => 'kbvideomate-floating-remove-on-close',
                    'style' => 'display:none;',
                    'hint' => $this->module->l('If enabled, closing the floating popup will remove it from the page until the next reload.', 'AdminKbVideoMateController')
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'AdminKbVideoMateController'),
            ]
        ];

        // Add select2 JS/CSS for better UX
        $this->addJqueryPlugin('select2');
        $this->addJS(_PS_JS_DIR_.'admin/select2.min.js');
        $this->addCSS(_PS_CSS_DIR_.'admin/select2.min.css');
        // Add custom CSS for widget radio images
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/widget-radio.css');
        // Add custom JS for dynamic widget field behavior
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/widget-form.js');
        // Add custom JS for dynamic entity field
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/entity-field.js');
        // Add form validation files
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/admin-form-validation.css');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/admin-form-validation.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/velovalidation.js');

        // Inject entity lists as JS variables for entity-field.js
        // These lists are used by the JS to populate entity selection fields dynamically based on the selected page type
        $productsList = [];
        foreach ($products as $p) {
            $productsList[] = ['id' => $p['id_product'], 'name' => $p['name']];
        }
        $categories = Category::getCategories($this->context->language->id, true, false);
        $categoriesList = [];
        foreach ($categories as $cat) {
            if (isset($cat['name']) && isset($cat['id_category'])) {
                $categoriesList[] = ['id' => $cat['id_category'], 'name' => $cat['name']];
            }
        }
        $cmsPages = CMS::listCms($this->context->language->id);
        $cmsList = [];
        foreach ($cmsPages as $cms) {
            $cmsList[] = ['id' => $cms['id_cms'], 'name' => $cms['meta_title']];
        }
        $manufacturers = Manufacturer::getManufacturers(false, $this->context->language->id, true);
        $manList = [];
        foreach ($manufacturers as $man) {
            $manList[] = ['id' => $man['id_manufacturer'], 'name' => $man['name']];
        }
        $suppliers = Supplier::getSuppliers(false, $this->context->language->id, true);
        $supList = [];
        foreach ($suppliers as $sup) {
            $supList[] = ['id' => $sup['id_supplier'], 'name' => $sup['name']];
        }
        // Pass all entity lists to JS using Media::addJsDef for use in dynamic entity selection
        Media::addJsDef([
            'kbProducts' => $productsList,
            'kbCategories' => $categoriesList,
            'kbCmsPages' => $cmsList,
            'kbManufacturers' => $manList,
            'kbSuppliers' => $supList,
        ]);
        // Add the admin token for AJAX controller to JS
        // This token is required for secure AJAX requests from JS to the AdminKbVideoMateAjaxController
        Media::addJsDef([
            'kbvideomate_token' => Tools::getAdminTokenLite('AdminKbVideoMateAjax'),
        ]);

        // Add velovalidation library and custom validation
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/velovalidation.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/admin-form-validation.js');
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/admin-form-validation.css');

        // Add validation messages
        Media::addJsDef([
            'kbvideomate_msg_required' => $this->module->l('This field is required', 'AdminKbVideoMateController'),
            'kbvideomate_msg_video_required' => $this->module->l('Video file is required', 'AdminKbVideoMateController'),
            'kbvideomate_msg_only_mp4' => $this->module->l('Only MP4 files are allowed', 'AdminKbVideoMateController'),
            'kbvideomate_msg_file_size' => $this->module->l('File size must be 50MB or less', 'AdminKbVideoMateController'),
            'kbvideomate_msg_select_video' => $this->module->l('Please select a video from the library', 'AdminKbVideoMateController'),
            'kbvideomate_msg_select_widget' => $this->module->l('Please select a widget type', 'AdminKbVideoMateController'),
            'kbvideomate_msg_max_visible' => $this->module->l('Please enter the maximum number of visible videos', 'AdminKbVideoMateController'),
            'kbvideomate_msg_max_visible_valid' => $this->module->l('Please enter a valid positive integer', 'AdminKbVideoMateController'),
            'kbvideomate_msg_autoplay' => $this->module->l('Please enter the autoplay duration in seconds', 'AdminKbVideoMateController'),
            'kbvideomate_msg_autoplay_valid' => $this->module->l('Please enter a valid positive integer', 'AdminKbVideoMateController'),
        ]);

        $formHtml = $this->module->renderFreeTierNotice() . parent::renderForm();

        /*
        * Free-tier build: gating scripts for controls that can't be gated by a
        * generic CSS class (per-value reverting for Page/Widget Type/Popup Position
        * and entity-count cap). All rely on the shared engine in
        * kbvideomate-upgrade-modal.js.
        */
        $formHtml .= <<<'HTML'
<script type="text/javascript">
(function ($) {
    $(document).ready(function () {
        if (window.kbvmInterceptRevertingChoice) {
            window.kbvmInterceptRevertingChoice(
                'select[name="page"]',
                ['category', 'best_seller', 'new_product', 'home'],
                function ($el) { return $el.val(); },
                function ($el, value) { $el.val(value).trigger('change'); }
            );
            window.kbvmInterceptRevertingChoice(
                'input[name="widget_type"]',
                ['carousel'],
                function () { return $('input[name="widget_type"]:checked').val(); },
                function ($el, value) {
                    $('input[name="widget_type"][value="' + value + '"]').prop('checked', true).trigger('change');
                }
            );
            window.kbvmInterceptRevertingChoice(
                'select[name="floating_position"]',
                ['left'],
                function ($el) { return $el.val(); },
                function ($el, value) { $el.val(value).trigger('change'); }
            );
        }

        /*
        * Free-tier build: cap a multi-select Select2 v3.x field at 1 selection by
        * blocking the 2nd pick outright, via the 'select2-selecting' event - this
        * fires BEFORE a pick is committed to the underlying <select> and is
        * cancelable, so calling preventDefault() here means the merchant is never
        * actually able to select more than 1 item at all (stricter than letting the
        * 2nd item get picked and reverting it afterwards). The popup shows on the
        * blocked attempt instead of after the fact.
        */
        function kbvmCapSelect2ToOne($select) {
            $select.on('select2-selecting', function (e) {
                var current = $select.val();
                if (Array.isArray(current) && current.length >= 1) {
                    e.preventDefault();
                    if (window.kbvmShowUpgradeModal) { window.kbvmShowUpgradeModal(); }
                }
            });
        }

        // Product Entity Selection is capped at 1.
        kbvmCapSelect2ToOne($('#kb-entity-select'));

        // Video (from Library) is capped at 1 the same way - Carousel is the only
        // widget type that would legitimately need more than one video, and Carousel
        // can never actually be saved in the Free build (rejected server-side, and
        // reverted client-side above), so no reachable widget type should ever be
        // able to persist more than one video.
        kbvmCapSelect2ToOne($('select[name^="id_kbvideomate_library"]'));

    });
})(jQuery);
</script>
HTML;

        return $formHtml;
    }

    public function postProcess()
    {
        /*
         * Normalize form values into controller properties instead of $_POST/GET/REQUEST
         * (PrestaShop Addons compliance). Applied in processUpdate() / processAdd().
         * 25-02-2025
         */
        if (Tools::isSubmit('submitAdd'.$this->table) || Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
            $video_ids = Tools::getValue('id_kbvideomate_library');
            $this->submitted_id_kbvideomate_library = is_array($video_ids) ? implode(',', $video_ids) : (string)$video_ids;

            $entity_ids = Tools::getValue('entity_ids');
            if (is_array($entity_ids)) {
                $entity_ids = array_unique($entity_ids);
                $this->submitted_entity_ids = implode(',', $entity_ids);
            } else {
                $this->submitted_entity_ids = $entity_ids !== null && $entity_ids !== '' ? (string)$entity_ids : '';
            }

            $this->submitted_widget_type = (string)Tools::getValue('widget_type');
        }
        parent::postProcess();
    }

    /**
     * Free-tier build: true when the submitted page/widget_type selection is
     * premium-only. Used to reject processAdd()/processUpdate() entirely (no save at
     * all) rather than silently swapping the value - client-side, renderForm()'s
     * inline script already reverts these choices and shows the upgrade modal before
     * a normal submit can ever carry them, so this is the defense-in-depth backstop
     * for a JS-bypassed submit.
     * @return bool
     */
    protected function isRestrictedWidgetChoice(): bool
    {
        $page = (string)Tools::getValue('page');
        if ($page !== '' && $page !== 'product') {
            return true;
        }
        $widgetType = $this->submitted_widget_type !== null ? $this->submitted_widget_type : (string)Tools::getValue('widget_type');
        return $widgetType === 'carousel';
    }

    /**
     * Apply normalized widget form values to object (no $_POST/GET/REQUEST usage).
     * 25-02-2025
     * @param KbVideoMateWidget $object
     */
    protected function applySubmittedValuesToWidget(KbVideoMateWidget $object): void
    {
        $videoIds = $this->submitted_id_kbvideomate_library !== null
            ? $this->submitted_id_kbvideomate_library
            : (string)Tools::getValue('id_kbvideomate_library');
        $videoIdList = array_values(array_filter(explode(',', $videoIds), function ($v) { return $v !== ''; }));
        /*
        * Free-tier build: Video (from Library) is capped at 1 the same way as
        * Product Entity Selection - Carousel is the only widget type that would
        * legitimately need more than one video, and isRestrictedWidgetChoice()
        * already rejects the whole save before this method ever runs if widget_type
        * is 'carousel', so by this point it's guaranteed to be a widget type that
        * should never persist more than one video. Primary enforcement is the
        * client-side select2-selecting block in renderForm(); this is
        * defense-in-depth for a JS-bypassed submit - it silently keeps only the
        * first video rather than rejecting the whole save.
        */
        if (count($videoIdList) > KbVideoMate::FREE_WIDGET_VIDEO_CAP) {
            $videoIdList = array_slice($videoIdList, 0, KbVideoMate::FREE_WIDGET_VIDEO_CAP);
        }
        $object->id_kbvideomate_library = pSQL(implode(',', $videoIdList));

        $entityIds = $this->submitted_entity_ids !== null ? $this->submitted_entity_ids : (string)Tools::getValue('entity_ids');
        $entityIdList = array_values(array_filter(explode(',', $entityIds), function ($v) { return $v !== ''; }));
        /*
        * Free-tier build: Product Entity Selection is capped at
        * KbVideoMate::FREE_ENTITY_CAP (1). Primary enforcement is the client-side
        * truncate-and-revert in renderForm()'s inline script; this is
        * defense-in-depth for a JS-bypassed submit - it silently keeps only the
        * first entity rather than rejecting the whole save.
        */
        if (count($entityIdList) > KbVideoMate::FREE_ENTITY_CAP) {
            $entityIdList = array_slice($entityIdList, 0, KbVideoMate::FREE_ENTITY_CAP);
        }
        $object->entity_ids = pSQL(implode(',', $entityIdList));

        /*
        * Free-tier build: Carousel is a premium widget type - isRestrictedWidgetChoice()
        * already rejects the whole save before this method ever runs if widget_type is
        * 'carousel', so no code path here ever needs to read max_visible/
        * carousel_autoplay_duration from the request; these are always the safe
        * defaults below.
        */
        $object->max_visible = 0;
        $object->carousel_autoplay_duration = 0;
        $widget_type = $this->submitted_widget_type !== null ? $this->submitted_widget_type : (string)Tools::getValue('widget_type');
        if ($widget_type !== 'product') {
            $object->video_as_main_image = 0;
        } else {
            $object->video_as_main_image = (int)Tools::getValue('video_as_main_image', $object->video_as_main_image);
        }

        /*
        * Free-tier build: Popup Position Left is premium - never persist a submitted
        * left value. Always force right (defense-in-depth for a JS-bypassed submit).
        */
        $object->floating_position = 'right';

        /*
        * Floating widget "remove on close" is available in the Free build - save the
        * submitted value.
        */
        $object->floating_remove_on_close = (int)Tools::getValue('floating_remove_on_close', 0);
    }

    /**
     * Process update using controller properties instead of $_POST (PrestaShop Addons compliance).
     * 25-02-2025
     */
    public function processUpdate()
    {
        $id = (int)Tools::getValue('id_'.$this->table);
        if (!$id) {
            return parent::processUpdate();
        }
        if ($this->isRestrictedWidgetChoice()) {
            Tools::redirectAdmin(self::$currentIndex.'&update'.$this->table.'&id_'.$this->table.'='.$id.'&token='.$this->token.'&kbvmLocked=1');
        }
        $object = new KbVideoMateWidget($id);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = $this->module->l('Object could not be loaded.', 'AdminKbVideoMateController');
            return false;
        }
        $this->copyFromPost($object, $this->table);
        $this->applySubmittedValuesToWidget($object);
        if ($object->update()) {
            $this->confirmations[] = $this->module->l('Update successful.', 'AdminKbVideoMateController');
            $this->redirect_after = self::$currentIndex.'&conf=4&token='.$this->token;
            return true;
        }
        $this->errors[] = $this->module->l('An error occurred while updating.', 'AdminKbVideoMateController');
        return false;
    }

    /**
     * Process add using controller properties instead of $_POST (PrestaShop Addons compliance).
     * 25-02-2025
     */
    public function processAdd()
    {
        if ($this->isRestrictedWidgetChoice()) {
            Tools::redirectAdmin(self::$currentIndex.'&add'.$this->table.'&token='.$this->token.'&kbvmLocked=1');
        }
        /*
        * Free-tier build: shop-wide widget-mapping cap (KbVideoMate::FREE_WIDGET_CAP,
        * 5 total, any widget_type combined). Primary enforcement is the client-side
        * Save-button intercept in renderForm(); this is defense-in-depth for a
        * JS-bypassed submit - reject entirely, nothing partial is created.
        */
        if ($this->module->kbvmHasReachedWidgetCap()) {
            Tools::redirectAdmin(self::$currentIndex.'&add'.$this->table.'&token='.$this->token.'&kbvmLocked=1');
        }
        $object = new KbVideoMateWidget();
        $this->copyFromPost($object, $this->table);
        $this->applySubmittedValuesToWidget($object);
        if ($object->add()) {
            $this->confirmations[] = $this->module->l('Creation successful.', 'AdminKbVideoMateController');
            $this->redirect_after = self::$currentIndex.'&conf=3&token='.$this->token;
            return true;
        }
        $this->errors[] = $this->module->l('An error occurred while creating.', 'AdminKbVideoMateController');
        return false;
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        // Add 'Add new widget' button to the left of Help
        $this->page_header_toolbar_btn['add_new_widget'] = array(
            'href' => self::$currentIndex.'&addkbvideomate&token='.$this->token,
            'desc' => $this->module->l('Add widget', 'AdminKbVideoMateController'),
            'icon' => 'process-icon-new'
        );
    }
} 