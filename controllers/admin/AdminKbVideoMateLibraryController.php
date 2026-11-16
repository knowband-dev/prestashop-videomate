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

// Use the kbvideomate library model
require_once __DIR__.'/../../classes/KbVideoMateLibrary.php';

/**
 * AdminKbVideoMateLibraryController
 * Handles the admin video library CRUD for kbvideomate
 */
class AdminKbVideoMateLibraryController extends ModuleAdminController
{
    /**
     * Stores computed form values for save; used instead of $_POST for PrestaShop Addons compliance.
     * Set in postProcess(), applied in processUpdate() / processAdd().
     * 25-02-2025
     * @var string|null
     */
    protected $submitted_video_url;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'kbvideomate_library';
        $this->className = 'KbVideoMateLibrary';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = true;
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

        // List fields for the library
        $this->fields_list = [
            'id_kbvideomate_library' => [
                'title' => $this->module->l('ID', 'AdminKbVideoMateLibraryController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'alias_name' => [
                'title' => $this->module->l('Alias Name', 'AdminKbVideoMateLibraryController'),
                'align' => 'left',
            ],
            'video_url' => [
                'title' => $this->module->l('Preview', 'AdminKbVideoMateLibraryController'),
                'align' => 'center',
                'callback' => 'getVideoPreviewButton',
                'search' => false,
                'orderby' => false,
            ],
            'status' => [
                'title' => $this->module->l('Status', 'AdminKbVideoMateLibraryController'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false,
                'ajax' => true,
            ],
            'date_add' => [
                'title' => $this->module->l('Date Added', 'AdminKbVideoMateLibraryController'),
                'align' => 'center',
                'type' => 'datetime',
            ],
        ];
        $this->_select = 'a.*';
        $this->_where = 'AND (a.id_kbvideomate_ai_video IS NULL OR a.id_kbvideomate_ai_video = 0)';

        // Add custom JS and CSS for video preview
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/video-preview.js');
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/video-preview.css');
        
        // Add velovalidation library and custom validation
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/velovalidation.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/video-form-validation.js');
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/admin-form-validation.css');
        
        // Add select2 for autocomplete functionality
        /*
        * Load Select2 CSS only here - JS will be loaded in renderForm after ensuring jQuery is available
        * 20-12-2024
        */
        $this->addCSS(_PS_CSS_DIR_.'admin/select2.min.css');

        // Add JS translation variables for error messages
        Media::addJsDef([
            'kbvideomate_msg_required' => $this->module->l('This field is required', 'AdminKbVideoMateLibraryController'),
            'kbvideomate_msg_video_required' => $this->module->l('Video file is required', 'AdminKbVideoMateLibraryController'),
            'kbvideomate_msg_only_mp4' => $this->module->l('Only MP4 files are allowed', 'AdminKbVideoMateLibraryController'),
            'kbvideomate_msg_file_size' => $this->module->l('File size must be 50MB or less', 'AdminKbVideoMateLibraryController'),
            'kbvideomate_msg_video_failed' => $this->module->l('Video failed to load.', 'AdminKbVideoMateLibraryController'),
        ]);
        
        // Add admin token for AJAX controller
        Media::addJsDef([
            'kbvideomate_ajax_token' => Tools::getAdminTokenLite('AdminKbVideoMateAjax'),
        ]);

        // Remove bulk actions
        $this->bulk_actions = [];
    }

    /*
     * Render video preview button via template; no inline HTML in controller.
     * @modifier Himanshu Vishwakarma
     * @date 10-02-2026
     */
    public static function getVideoPreviewButton($video_url, $row)
    {
        if (empty($video_url)) {
            return '';
        }
        $smarty = Context::getContext()->smarty;
        $smarty->assign('video_url', $video_url);
        return $smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_button.tpl');
    }

    /**
     * Render the list with the video preview modal
     */
    public function renderList()
    {
        /*
        * Free-tier build: shop-wide video library cap (KbVideoMate::FREE_LIBRARY_CAP,
        * 5 total) - surface it as a native PrestaShop header warning on the list page
        * whenever it's reached (also covers the redirect-back-to-list after a blocked
        * Add attempt below).
        */
        if ($this->module->kbvmHasReachedLibraryCap()) {
            $this->warnings[] = $this->module->l('No more form submissions are being accepted - the free version limit has been reached. Upgrade to the premium version for more submissions.', 'AdminKbVideoMateLibraryController');
        }

        $this->context->smarty->assign([
            'video_preview_modal' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_modal.tpl')
        ]);
        return $this->module->renderFreeTierNotice()
            . parent::renderList()
            . $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_modal.tpl');
    }

    /**
     * Render the add/edit form for the video library
     */
    public function renderForm()
    {
        /*
        * Free-tier build: shop-wide video library cap (KbVideoMate::FREE_LIBRARY_CAP,
        * 5 total) - block the Add form from ever rendering once reached (editing an
        * existing video never creates a new row, so it's unaffected). Redirect back to
        * the list with kbvmLocked=1 so the shared JS engine shows the upgrade modal on
        * load, same pattern used for every other server-side reject in this module.
        */
        if (!Tools::getValue('id_kbvideomate_library') && $this->module->kbvmHasReachedLibraryCap()) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&kbvmLocked=1');
        }

        // Load selected product and categories if editing
        $selectedProduct = null;
        $selectedCategories = [];
        if ($id_library = Tools::getValue('id_kbvideomate_library')) {
            $library = new KbVideoMateLibrary($id_library);
            if (Validate::isLoadedObject($library)) {
                // Get product_id and category_id from the object
                if (isset($library->product_id) && $library->product_id) {
                    $product = new Product((int)$library->product_id, false, $this->context->language->id);
                    if (Validate::isLoadedObject($product)) {
                        $selectedProduct = ['id' => $product->id, 'name' => $product->name];
                    }
                }
                if (isset($library->category_id) && $library->category_id !== '' && $library->category_id !== null) {
                    /*
                     * Handle single category ID; category_id may be int or string from DB - cast to string before strpos.
                     * @modifier Himanshu Vishwakarma
        	     * @date 19-02-2026
                     */
                    $catId = null;
                    if (is_array($library->category_id)) {
                        $catId = (int)$library->category_id[0];
                    } else {
                        $catIdStr = (string)$library->category_id;
                        $catId = strpos($catIdStr, ',') !== false ? (int)explode(',', $catIdStr)[0] : (int)$catIdStr;
                    }
                    if ($catId > 0) {
                        $category = new Category($catId, $this->context->language->id);
                        if (Validate::isLoadedObject($category)) {
                            $selectedCategories[] = ['id' => $category->id, 'name' => $category->name];
                        }
                    }
                }
            }
        }
        
        // Pass selected values to JS
        if ($selectedProduct) {
            Media::addJsDef(['kbSelectedProduct' => $selectedProduct]);
        }
        if (!empty($selectedCategories)) {
            Media::addJsDef(['kbSelectedCategories' => $selectedCategories]);
        }

        $isEdit = ($id_library && isset($library) && Validate::isLoadedObject($library));
        if ($isEdit) {
            $this->fields_value = $this->fields_value ?? [];
            $this->fields_value['custom_url'] = $library->redirection_url ?: '';
            $this->fields_value['redirection_type'] = ($library->redirection_type == 0) ? 'o' : (string)$library->redirection_type;
            /*
             * Pre-populate alias_name and video_url for edit form so they are submitted via hidden fields
             * instead of writing to $_POST in postProcess (PrestaShop Addons compliance)
             * 25-02-2025
             */
            $this->fields_value['alias_name'] = $library->alias_name;
            $this->fields_value['video_url'] = $library->video_url;
        }

        $inputFields = [];
        if ($isEdit) {
            /*
             * Hidden fields for alias_name and video_url on edit so values are preserved on submit
             * without using $_POST (PrestaShop Addons compliance)
             * 25-02-2025
             */
            $inputFields = [
                [
                    'type' => 'hidden',
                    'name' => 'alias_name',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'video_url',
                ],
            ];
        } else {
            $inputFields = [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Alias Name', 'AdminKbVideoMateLibraryController'),
                    'name' => 'alias_name',
                    'required' => true,
                    'desc' => $this->module->l('Enter a short, descriptive name for this video.', 'AdminKbVideoMateLibraryController'),
                    'hint' => $this->module->l('This name is for internal reference only and will not be shown to customers.', 'AdminKbVideoMateLibraryController')
                ],
                [
                    'type' => 'select',
                    'class' => 'general_tab',
                    'label' => $this->module->l('Video Uploade Type', 'AdminKbVideoMateLibraryController'),
                    'name' => 'video_type',
                    'required' => false,
                    'is_bool' => true,
                    'options' => [
                        'query' => array(
                            array(
                                'id_type' => '1',
                                'name' => $this->module->l('Upload','AdminKbVideoMateLibraryController')
                            ),
                            array(
                                'id_type' => '2',
                                'name' => $this->module->l('URL','AdminKbVideoMateLibraryController')
                            ),
                        ),
                        'id' => 'id_type',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'file',
                    'label' => $this->module->l('Upload Video', 'AdminKbVideoMateLibraryController'),
                    'name' => 'video_file',
                    'required' => true,
                    'display_image' => false,
                    'desc' => $this->module->l('Upload a video file in MP4 format (maximum size: 50MB).', 'AdminKbVideoMateLibraryController'),
                    'hint' => $this->module->l('Only MP4 files up to 50MB are allowed. For best results, use a 16:9 aspect ratio.', 'AdminKbVideoMateLibraryController')
                ],
                [
                    'label' => $this->module->l('Enter Custom  Video URL', 'AdminKbVideoMateLibraryController'),
                    'type' => 'text',
                    'class' => 'general_tab',
                    'name' => 'custom_video_url',
                    'required' => true,
                    'desc' => $this->module->l('Enter URL of the video type mp4', 'AdminKbVideoMateLibraryController'),
                ],
            ];
        }
       
        $this->fields_form = [
            'legend' => [
                'title' => $isEdit ? $this->module->l('Edit Video', 'AdminKbVideoMateLibraryController') : $this->module->l('Add/Edit Video', 'AdminKbVideoMateLibraryController'),
                'icon' => 'icon-video-camera'
            ],
            'input' => array_merge($inputFields, [
                [
                    'type' => 'select',
                    // Free-tier build: Redirection on Videos is a premium feature - the
                    // 3 real redirection options stay in the dropdown for upsell, but
                    // selecting one reverts back to "No Redirection" and shows the
                    // upgrade modal (see the inline kbvmInterceptRevertingChoice script
                    // in renderForm() below) rather than the generic locked-toggle
                    // class. A plain locked-toggle here would leave the visible value
                    // on e.g. "Product" while product_id stays unfillable (readonly is
                    // a no-op on <select> and the field is locked), which would trip
                    // video-form-validation.js's "product_id required when
                    // redirection_type=1" client-side check and block Save entirely -
                    // reverting avoids that. applySubmittedValuesToObject()/
                    // processAdd() below also always force-write 'No Redirection'
                    // server-side regardless of what was submitted.
                    'class' => 'general_tab',
                    'label' => $this->module->l('Redirection Type', 'AdminKbVideoMateLibraryController'),
                    'name' => 'redirection_type',
                    'required' => false,
                    'is_bool' => true,
                    'options' => [
                        'query' => array(
                            array(
                                'id_type' => 'o',
                                'name' => $this->module->l('No Redirection','AdminKbVideoMateLibraryController')
                            ),
                            array(
                                'id_type' => '1',
                                'name' => $this->module->l('Product','AdminKbVideoMateLibraryController')
                            ),
                            array(
                                'id_type' => '2',
                                'name' => $this->module->l('Categories','AdminKbVideoMateLibraryController')
                            ),
                            array(
                                'id_type' => '3',
                                'name' => $this->module->l('Custom URL','AdminKbVideoMateLibraryController')
                            ),
                        ),
                        'id' => 'id_type',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Select redirect for the video', 'AdminKbVideoMateLibraryController'),
                    'hint' => $this->module->l('Whatever redirection is selected the on video visit button will redirect to that page. This is a premium feature.', 'AdminKbVideoMateLibraryController')
                ],
                [
                    'label' => $this->module->l('Select Product', 'AdminKbVideoMateLibraryController'),
                    'type' => 'select',
                    'class' => 'general_tab kb-product-autocomplete kbvm-locked-field',
                    'name' => 'product_id',
                    'id' => 'kb-product-select',
                    'required' => false,
                    'readonly' => true,
                    'options' => [
                        'query' => [],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Search and select a product.', 'AdminKbVideoMateLibraryController'),
                ],
                [
                    'label' => $this->module->l('Select Category', 'AdminKbVideoMateLibraryController'),
                    'type' => 'select',
                    'class' => 'general_tab kb-category-autocomplete kbvm-locked-field',
                    'name' => 'category_id',
                    'id' => 'kb-category-select',
                    'required' => false,
                    'readonly' => true,
                    'options' => [
                        'query' => [],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Search and select a category.', 'AdminKbVideoMateLibraryController'),
                ],
                [
                    'label' => $this->module->l('Enter Custom Url', 'AdminKbVideoMateLibraryController'),
                    'type' => 'text',
                    'class' => 'general_tab kbvm-locked-field',
                    'name' => 'custom_url',
                    'required' => false,
                    'readonly' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Status', 'AdminKbVideoMateLibraryController'),
                    'name' => 'status',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'status_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminKbVideoMateLibraryController')
                        ],
                        [
                            'id' => 'status_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminKbVideoMateLibraryController')
                        ]
                    ],
                    'desc' => $this->module->l('Enable or disable this video.', 'AdminKbVideoMateLibraryController'),
                    'hint' => $this->module->l('If disabled, this video will not be available for widgets on the front office.', 'AdminKbVideoMateLibraryController')
                ],
            ]),
            'submit' => [
                'title' => $this->module->l('Save', 'AdminKbVideoMateLibraryController'),
            ]
        ];
        
        /*
        * Load Select2 and autocomplete script in renderForm
        * Use addJqueryPlugin which ensures jQuery loads first, then Select2
        * 20-12-2024
        */
        $this->addJqueryPlugin('select2');
        $this->addJS(_PS_JS_DIR_.'admin/select2.min.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/library-autocomplete.js');

        $formHtml = $this->module->renderFreeTierNotice() . parent::renderForm();

        /*
        * Free-tier build: revert Redirection Type back to "No Redirection" whenever a
        * premium option is chosen, and show the upgrade modal - see the class
        * comment above the redirection_type field definition for why a revert
        * (rather than the generic locked-toggle) is required here.
        */
        $formHtml .= <<<'HTML'
<script type="text/javascript">
(function ($) {
    $(document).ready(function () {
        if (window.kbvmInterceptRevertingChoice) {
            window.kbvmInterceptRevertingChoice(
                'select[name="redirection_type"]',
                ['1', '2', '3'],
                function ($el) { return $el.val(); },
                function ($el, value) { $el.val(value).trigger('change'); }
            );
        }
    });
})(jQuery);
</script>
HTML;

        return $formHtml;
    }

    /**
     * Handle form submission and file upload
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            /*
             * Compute form values into controller properties instead of $_POST/GET/REQUEST
             * (PrestaShop Addons compliance). Applied in processUpdate() / processAdd().
             * 25-02-2025
             */
            $video_file = isset($_FILES['video_file']) ? $_FILES['video_file'] : null;
            if ($video_file && $video_file['error'] === 0) {
                $allowed_extensions = ['mp4', 'webm', 'ogg'];
                $file_extension = strtolower(pathinfo($video_file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_extension, $allowed_extensions)) {
                    $this->errors[] = $this->module->l('Only MP4, WebM, or OGG video files are allowed.', 'AdminKbVideoMateLibraryController');
                } else {
                    $upload_dir = _PS_IMG_DIR_.'kbvideos/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $new_filename = 'video_'.uniqid().'.'.$file_extension;
                    $upload_path = $upload_dir.$new_filename;
                    if (move_uploaded_file($video_file['tmp_name'], $upload_path)) {
                        $this->submitted_video_url = $this->context->link->getBaseLink().'img/kbvideos/'.$new_filename;
                    } else {
                        $this->errors[] = $this->module->l('Error uploading video file. Please check directory permissions.', 'AdminKbVideoMateLibraryController');
                    }
                }
            }

            $video_type = Tools::getValue('video_type');
            if ($video_type == '2') {
                $custom_video_url = Tools::getValue('custom_video_url');
                if ($custom_video_url && Validate::isUrl($custom_video_url)) {
                    $this->submitted_video_url = $custom_video_url;
                }
            }
        }
        return parent::postProcess();
    }

    /**
     * Apply computed form values to library object (no $_POST usage).
     * 25-02-2025
     * @param KbVideoMateLibrary $object
     */
    protected function applySubmittedValuesToObject(KbVideoMateLibrary $object): void
    {
        $object->alias_name = pSQL(Tools::getValue('alias_name', $object->alias_name));
        if ($this->submitted_video_url !== null) {
            $object->video_url = pSQL($this->submitted_video_url);
        } elseif ($object->video_url) {
            $object->video_url = pSQL($object->video_url);
        } else {
            $object->video_url = pSQL((string)Tools::getValue('video_url'));
        }
        /*
        * Free-tier build: Redirection on Videos is a premium feature - the
        * fields are display-only in renderForm() ('kbvm-locked-toggle'/
        * 'kbvm-locked-field') and no code path in this controller reads
        * product_id/category_id/redirection_type/redirection_url from the
        * request, so these are always the safe defaults below.
        */
        $object->product_id = 0;
        $object->category_id = 0;
        $object->redirection_type = 0;
        $object->redirection_url = '';
        $status = (int)Tools::getValue('status', $object->status);
        $object->status = $status;
        $object->active = $status;
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
        $object = new KbVideoMateLibrary($id);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = $this->module->l('Object could not be loaded.', 'AdminKbVideoMateLibraryController');
            return false;
        }
        $this->applySubmittedValuesToObject($object);
        if ($object->update()) {
            $this->confirmations[] = $this->module->l('Update successful.', 'AdminKbVideoMateLibraryController');
            $this->redirect_after = self::$currentIndex.'&conf=4&token='.$this->token;
            return true;
        }
        $this->errors[] = $this->module->l('An error occurred while updating.', 'AdminKbVideoMateLibraryController');
        return false;
    }

    /**
     * Process add using controller properties instead of $_POST (PrestaShop Addons compliance).
     * 25-02-2025
     */
    public function processAdd()
    {
        /*
        * Free-tier build: shop-wide video library cap (KbVideoMate::FREE_LIBRARY_CAP,
        * 5 total). Primary enforcement is renderForm() blocking the Add form from ever
        * opening; this is defense-in-depth for a JS-bypassed or directly-posted submit.
        */
        if ($this->module->kbvmHasReachedLibraryCap()) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&kbvmLocked=1');
        }
        $object = new KbVideoMateLibrary();
        $object->alias_name = pSQL((string)Tools::getValue('alias_name'));
        if ($this->submitted_video_url !== null) {
            $object->video_url = pSQL($this->submitted_video_url);
        } else {
            $object->video_url = pSQL((string)Tools::getValue('custom_video_url', ''));
        }
        /*
        * Free-tier build: same as applySubmittedValuesToObject() above - Redirection
        * on Videos is premium and never persisted from submitted values.
        */
        $object->product_id = 0;
        $object->category_id = 0;
        $object->redirection_type = 0;
        $object->redirection_url = '';
        $status = (int)Tools::getValue('status', 1);
        $object->status = $status;
        $object->active = $status;
        if ($object->add()) {
            $this->confirmations[] = $this->module->l('Creation successful.', 'AdminKbVideoMateLibraryController');
            $this->redirect_after = self::$currentIndex.'&conf=3&token='.$this->token;
            return true;
        }
        $this->errors[] = $this->module->l('An error occurred while creating.', 'AdminKbVideoMateLibraryController');
        return false;
    }

    /**
     * AJAX status toggle for kbvideomate library
     */
    public function ajaxProcessStatusKbvideomateLibrary()
    {
        if (!$id_video = (int)Tools::getValue('id_kbvideomate_library')) {
            die(json_encode([
                'success' => false,
                'error' => true,
                'text' => $this->module->l('Failed to update the status', 'AdminKbVideoMateLibraryController')
            ]));
        }
        $video = new KbVideoMateLibrary($id_video);
        if (Validate::isLoadedObject($video)) {
            $video->status = (int) !(int) $video->status;
            if ($video->update()) {
                die(json_encode([
                    'success' => true,
                    'text' => $this->module->l('The status has been updated successfully', 'AdminKbVideoMateLibraryController')
                ]));
            }
        }
        die(json_encode([
            'success' => false,
            'error' => true,
            'text' => $this->module->l('An error occurred while updating the status', 'AdminKbVideoMateLibraryController')
        ]));
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        // Add 'Add video' button to the left of Help
        $this->page_header_toolbar_btn['add_new_video'] = array(
            'href' => self::$currentIndex.'&addkbvideomate_library&token='.$this->token,
            'desc' => $this->module->l('Add video', 'AdminKbVideoMateLibraryController'),
            'icon' => 'process-icon-new'
        );
    }
} 