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

require_once _PS_MODULE_DIR_.'kbvideomate/classes/KbVideoMateAiVideo.php';

/*
* AI Video Library admin controller to queue AI-generated videos for products.
* @modifier Himanshu Vishwakarma
* @date 11-02-2026
*/
class AdminKbVideoMateAiLibraryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'kbvideomate_ai_video';
        $this->className = 'KbVideoMateAiVideo';
        $this->identifier = 'id_kbvideomate_ai_video';
        $this->lang = false;
        $this->addRowAction('viewError');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = false;
        $this->deleted = false;
        $this->no_link = true;
        $this->context = Context::getContext();
        parent::__construct();

        $module = Module::getInstanceByName('kbvideomate');
        if ($module instanceof Module) {
            $this->module = $module;
        } else {
            throw new PrestaShopException('Module kbvideomate is not installed or could not be loaded.');
        }

        /*
        * Configure AI video queue listing to show product, status and preview.
        * @modifier Himanshu Vishwakarma
        * @date 10-02-2026
        */
        $this->fields_list = [
            'id_kbvideomate_ai_video' => [
                'title' => $this->module->l('ID', 'AdminKbVideoMateAiLibraryController'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_product' => [
                'title' => $this->module->l('Product', 'AdminKbVideoMateAiLibraryController'),
                'align' => 'left',
                'callback' => 'getProductName',
                'search' => false,
                'orderby' => false,
            ],
            'queue_status' => [
                'title' => $this->module->l('Progress Status', 'AdminKbVideoMateAiLibraryController'),
                'align' => 'center',
                'callback' => 'getQueueStatusWithError',
                'search' => false,
                'orderby' => false,
            ],
            'video_url' => [
                'title' => $this->module->l('Preview', 'AdminKbVideoMateAiLibraryController'),
                'align' => 'center',
                'callback' => 'getVideoPreviewButton',
                'search' => false,
                'orderby' => false,
            ],
            'active' => [
                'title' => $this->module->l('Status', 'AdminKbVideoMateAiLibraryController'),
                'active' => 'active',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'ajax' => true,
                'orderby' => false,
            ],
            'date_add' => [
                'title' => $this->module->l('Date Added', 'AdminKbVideoMateAiLibraryController'),
                'align' => 'center',
                'type' => 'datetime',
            ],
        ];

        $this->_select = 'a.*';

        /*
        * Reuse video preview assets so AI Video Library can show inline preview just like Video Library.
        * @modifier Himanshu Vishwakarma
        * @date 11-02-2026
        */
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/video-preview.js');
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/video-preview.css');
        $this->addJqueryPlugin('fancybox');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/ai-video-error.js');
        Media::addJsDef([
            'kbvideomate_msg_video_failed' => $this->module->l('Video failed to load.', 'AdminKbVideoMateAiLibraryController'),
        ]);

        /*
         * Add bulk action to set Progress status of selected items to Pending.
         * @modifier Himanshu Vishwakarma
         * @date 19-02-2026
         */
        $this->bulk_actions['update_status_pending'] = [
            'text' => $this->module->l('Update status to Pending', 'AdminKbVideoMateAiLibraryController'),
            'icon' => 'icon-time',
        ];
    }

    /*
     * Row action: View Error in Actions column; returns null when no error, else link+hidden div for Fancybox.
     * @modifier Himanshu Vishwakarma
     * @date 10-02-2026
     */
    public function displayViewErrorLink($token = null, $id = null, $name = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return null;
        }
        $queue = new KbVideoMateAiVideo($id);
        if (!Validate::isLoadedObject($queue)) {
            return null;
        }
        $err = trim((string)($queue->error_message ?? ''));
        if ($err === '' || $queue->queue_status !== 'failed') {
            return null;
        }
        $href = 'kbvideomate-error-' . $id;
        $this->context->smarty->assign([
            'href' => $href,
            'action' => $this->module->l('View Error', 'AdminKbVideoMateAiLibraryController'),
            'icon' => 'warning-sign',
            'text' => $err,
        ]);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_ai_video_error_link.tpl');
    }

    /*
     * Display queue status only; error details are shown via View Error action.
     * @modifier Himanshu Vishwakarma
     * @date 10-02-2026
     */
    public function getQueueStatusWithError($status, $row)
    {
        return (string)($status ?? '');
    }

    public function getProductName($idProduct, $row)
    {
        $product = new Product((int)$idProduct, false, $this->context->language->id);
        if (Validate::isLoadedObject($product)) {
            return $product->name;
        }
        return (int)$idProduct;
    }

    /**
     * Render a preview button when video_url is available (markup in template; no HTML in controller).
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
     * Ensure video preview modal and content are in page output for list view (match Video Library behavior).
     * @modifier Himanshu Vishwakarma
     * @date 10-02-2026
     */
    public function initContent()
    {
        parent::initContent();
        if ($this->display === '' || $this->display === 'list') {
            $modalHtml = $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_modal.tpl');
            $this->content .= $modalHtml;
        }
    }

    /**
     * Render the list with potential modal support (reuse existing preview modal).
     * @modifier Himanshu Vishwakarma
     * @date 11-02-2026
     */
    public function renderList()
    {
        $this->context->smarty->assign([
            'video_preview_modal' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_modal.tpl')
        ]);

        /*
        * Free-tier build: AI Video Generation is a premium feature, but this list can
        * legitimately hold real historical rows (e.g. a shop downgraded from Paid) -
        * show them read-only (view error / delete / active-toggle stay functional,
        * they cost nothing to keep) rather than force-emptying the list. Only the
        * "Add" action is locked - see postProcess() and renderForm().
        */
        return $this->module->renderFreeTierNotice()
            . parent::renderList()
            . $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_video_preview_modal.tpl');
    }

    /*
     * AJAX status toggle for AI Video Library; enables/disables queue entry on click.
     * @modifier Himanshu Vishwakarma
     * @date 10-02-2026
     */
    public function ajaxProcessActiveKbvideomateAiVideo()
    {
        if (!$id = (int)Tools::getValue('id_kbvideomate_ai_video')) {
            die(json_encode([
                'success' => false,
                'error' => true,
                'text' => $this->module->l('Failed to update the status', 'AdminKbVideoMateAiLibraryController')
            ]));
        }
        $queue = new KbVideoMateAiVideo($id);
        if (Validate::isLoadedObject($queue)) {
            $queue->active = (int)!(int)$queue->active;
            if ($queue->update()) {
                die(json_encode([
                    'success' => true,
                    'text' => $this->module->l('The status has been updated successfully', 'AdminKbVideoMateAiLibraryController')
                ]));
            }
        }
        die(json_encode([
            'success' => false,
            'error' => true,
            'text' => $this->module->l('An error occurred while updating the status', 'AdminKbVideoMateAiLibraryController')
        ]));
    }

    public function renderForm()
    {
        $this->addJqueryPlugin('select2');
        $this->addJS(_PS_JS_DIR_.'admin/select2.min.js');
        $this->addCSS(_PS_CSS_DIR_.'admin/select2.min.css');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/entity-field.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/velovalidation.js');
        $this->addJS(_MODULE_DIR_.'kbvideomate/views/js/ai-video-duration-validation.js');
        $this->addCSS(_MODULE_DIR_.'kbvideomate/views/css/admin-form-validation.css');

        /*
        * Free-tier build: AI Video Generation is a premium feature - the "Add" form
        * always stays locked, regardless of the KBVIDEOMATE_AI_VIDEO_ENABLED config
        * value (which may still read 1 immediately after a Paid-to-Free file swap,
        * before the merchant re-saves General Settings).
        */
        $generalSettingsUrl = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => 'kbvideomate']);
        $enableAiErrorMsg = $this->module->l(
            'AI Video Generation is available in the premium version.',
            'AdminKbVideoMateAiLibraryController'
        );

        Media::addJsDef([
            'kbvideomate_token' => Tools::getAdminTokenLite('AdminKbVideoMateAjax'),
            'kbEntityDefaultPage' => 'product',
            'kbvideomate_msg_required' => $this->module->l('This field is required', 'AdminKbVideoMateAiLibraryController'),
            'kbvideomate_general_settings_url' => $generalSettingsUrl,
            'kbvideomate_enable_ai_error_msg' => $enableAiErrorMsg,
            'kbvideomate_ai_platform' => (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_PLATFORM'),
            'kbvideomate_ai_model' => (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_MODEL'),
            'kbvideomate_duration_veo2' => $this->module->l('Duration must be between 5 and 8 seconds for Gemini Veo 2.', 'AdminKbVideoMateAiLibraryController'),
            'kbvideomate_duration_veo3' => $this->module->l('Duration must be between 4 and 8 seconds for Gemini Veo 3.', 'AdminKbVideoMateAiLibraryController'),
            'kbvideomate_duration_openai' => $this->module->l('Duration must be between 4 and 12 seconds for OpenAI.', 'AdminKbVideoMateAiLibraryController'),
        ]);

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('AI Videos', 'AdminKbVideoMateAiLibraryController'),
                'icon' => 'icon-video-camera',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Products', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'id_products[]',
                    'multiple' => true,
                    'id' => 'kb-entity-select',
                    'class' => 'select2 kb-entity-select',
                    'required' => true,
                    'options' => [
                        'query' => [],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->module->l('Select one or more products for which AI videos should be generated.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->module->l('Custom Prompt', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'custom_prompt',
                    'required' => true,
                    'rows' => 4,
                    'cols' => 60,
                    'desc' => $this->module->l('Required. The prompt is built from this field; placeholders are replaced when generating: {product_name}, {product_description}, {upc}.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Video Duration (seconds)', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'video_duration',
                    'desc' => $this->module->l('Set the video duration in seconds according to your server cron execution limit. Choose a shorter duration (e.g. 5–8 seconds) if your cron job times out before video generation completes.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Include Product Image', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'use_product_image',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'use_product_image_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                        [
                            'id' => 'use_product_image_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                    ],
                    'desc' => $this->module->l('If enabled, the product image will be used as a visual reference for video generation.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Include Product Description', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'use_product_description',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'use_product_description_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                        [
                            'id' => 'use_product_description_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                    ],
                    'desc' => $this->module->l('If enabled, the product description will be used as part of the AI prompt.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Include Product Name', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'use_product_name',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'use_product_name_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                        [
                            'id' => 'use_product_name_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                    ],
                    'desc' => $this->module->l('If enabled, the product name will be included in the AI prompt.', 'AdminKbVideoMateAiLibraryController'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Active', 'AdminKbVideoMateAiLibraryController'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminKbVideoMateAiLibraryController'),
                        ],
                    ],
                    'desc' => $this->module->l('Control whether this AI video queue entry is active.', 'AdminKbVideoMateAiLibraryController'),
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'AdminKbVideoMateAiLibraryController'),
            ],
        ];

        /*
        * Prefill prompt field with reference prompt on add form; prompt is always built from DB value.
        * @modifier Himanshu Vishwakarma
        * @date 10-02-2026
        */
        $this->fields_value = $this->fields_value ?? [];
        if (empty($this->fields_value['custom_prompt']) && empty($this->fields_value['kbvideomate_ai_video_custom_prompt'])) {
            $this->fields_value['custom_prompt'] = $this->getDefaultReferencePrompt();
        }

        $formHtml = parent::renderForm();

        /*
        * Free-tier build: always show the premium-feature alert (not conditioned on
        * $aiVideoEnabled - see comment above) and always intercept the Save button via
        * the shared upgrade-modal engine, in the capture phase, so it wins over
        * ai-video-settings.js's own submit handling regardless of script load order.
        * The switch fields below (Include Image/Description/Name, Active) are
        * intercepted the same way, on `click` rather than `change`, so the toggle
        * never visibly flips to on - the whole Add form is locked, so unlike the
        * change-based gating used elsewhere (e.g. the AI-enable switch in General
        * Settings) there is no legitimate "let it flip, force-reset on save" case here.
        */
        $this->context->smarty->assign([
            'enable_ai_error_msg' => $enableAiErrorMsg,
            'general_settings_url' => $generalSettingsUrl,
        ]);
        $alertHtml = $this->context->smarty->fetch(_PS_MODULE_DIR_.'kbvideomate/views/templates/admin/_ai_video_disabled_alert.tpl');
        $lockScript = '<script type="text/javascript">if (window.kbvmInterceptGatedControl) { window.kbvmInterceptGatedControl(\'button[name^="submitAdd"], input[name^="submitAdd"], '
            . '#use_product_image_on, #use_product_image_off, #use_product_description_on, #use_product_description_off, '
            . '#use_product_name_on, #use_product_name_off, #active_on, #active_off\'); }</script>';
        $formHtml = $this->module->renderFreeTierNotice() . $alertHtml . $formHtml . $lockScript;

        return $formHtml;
    }

    public function postProcess()
    {
        /*
         * Handle bulk enable/disable in postProcess so selection is processed and user gets redirect confirmation.
         * Legacy list sends submitBulkenableSelection{table} or submitBulkdisableSelection{table}; checkboxes use {table}Box.
         * @modifier Himanshu Vishwakarma
         * @date 19-02-2026
         */
        $bulkEnable = Tools::isSubmit('submitBulkenableSelection' . $this->table);
        $bulkDisable = Tools::isSubmit('submitBulkdisableSelection' . $this->table);
        if ($bulkEnable || $bulkDisable) {
            $ids = Tools::getValue($this->table . 'Box');
            if (!is_array($ids) && $ids !== null && $ids !== '') {
                $ids = array_map('intval', array_filter(explode(',', (string) $ids)));
            }
            if (is_array($ids) && !empty($ids)) {
                $active = $bulkEnable ? 1 : 0;
                foreach ($ids as $id) {
                    $id = (int) $id;
                    if ($id <= 0) {
                        continue;
                    }
                    $queue = new KbVideoMateAiVideo($id);
                    if (Validate::isLoadedObject($queue)) {
                        $queue->active = $active;
                        $queue->update();
                    }
                }
            }
            Tools::redirectAdmin(self::$currentIndex . '&conf=6&token=' . $this->token);
        }

        /*
         * Bulk set Progress status to Pending for selected AI video queue entries.
         * @modifier Himanshu Vishwakarma
         * @date 19-02-2026
         */
        if (Tools::isSubmit('submitBulkupdate_status_pending' . $this->table)) {
            $ids = Tools::getValue($this->table . 'Box');
            if (!is_array($ids) && $ids !== null && $ids !== '') {
                $ids = array_map('intval', array_filter(explode(',', (string) $ids)));
            }
            if (is_array($ids) && !empty($ids)) {
                foreach ($ids as $id) {
                    $id = (int) $id;
                    if ($id <= 0) {
                        continue;
                    }
                    $queue = new KbVideoMateAiVideo($id);
                    if (Validate::isLoadedObject($queue)) {
                        $queue->queue_status = 'pending';
                        $queue->date_upd = date('Y-m-d H:i:s');
                        $queue->update();
                    }
                }
            }
            Tools::redirectAdmin(self::$currentIndex . '&conf=6&token=' . $this->token);
        }

        if (Tools::isSubmit('submitAddkbvideomate_ai_video')) {
            /*
            * Free-tier build: AI Video Generation is a premium feature - the Save
            * button is intercepted client-side (see renderForm()), but a JS-bypassed
            * submit still needs to be blocked here. Redirect with kbvmLocked=1 instead
            * of setting $this->errors[] so the shared JS engine shows the upgrade
            * modal on the reloaded page rather than a PrestaShop error banner - no
            * queue row is ever inserted.
            */
            Tools::redirectAdmin(self::$currentIndex.'&addkbvideomate_ai_video&token='.$this->token.'&kbvmLocked=1');
        }

        return parent::postProcess();
    }

    public function processBulkEnableSelection()
    {
        $ids = Tools::getValue($this->table.'Box');
        if (!is_array($ids) || empty($ids)) {
            return;
        }
        foreach ($ids as $id) {
            $queue = new KbVideoMateAiVideo((int)$id);
            if (Validate::isLoadedObject($queue)) {
                $queue->active = 1;
                $queue->update();
            }
        }
    }

    public function processBulkDisableSelection()
    {
        $ids = Tools::getValue($this->table.'Box');
        if (!is_array($ids) || empty($ids)) {
            return;
        }
        foreach ($ids as $id) {
            $queue = new KbVideoMateAiVideo((int)$id);
            if (Validate::isLoadedObject($queue)) {
                $queue->active = 0;
                $queue->update();
            }
        }
    }

    private function getDefaultReferencePrompt(): string
    {
        return 'Create a high-quality, professional product video for "{product_name}". '
            . 'Show the product clearly, positioned centrally in the frame with brand and details visible. '
            . 'Use a clean, neutral background that suits the product. '
            . 'Include slow, smooth camera movements (gentle rotation, zoom, or panning) to showcase different angles. '
            . 'Professional lighting and styling suitable for an e-commerce product page. '
            . 'Product context: {product_description}';
    }
}

