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

// Ensure PrestaShop context
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Main class for the VideoMate module
 * Handles installation, uninstallation, and core logic
 */
class KbVideoMate extends Module
{
    const PARENT_TAB_CLASS = 'AdminKbVideoMateParent';
    const SELL_CLASS_NAME = 'SELL';

    /**
     * Free-tier build: shop-wide cap on total widget mappings (kbvideomate rows),
     * across every widget_type combined - not a separate cap per type.
     */
    const FREE_WIDGET_CAP = 5;

    /**
     * Free-tier build: shop-wide cap on total video library entries (kbvideomate_library
     * rows), excluding AI-generated rows which can never be created in the Free build.
     */
    const FREE_LIBRARY_CAP = 5;

    /**
     * Free-tier build: max number of entities a single widget mapping may target.
     */
    const FREE_ENTITY_CAP = 1;

    /**
     * Free-tier build: max number of library videos a single widget mapping may
     * reference - Carousel is the only widget type that would legitimately need
     * more than one, and Carousel can never be saved in the Free build.
     */
    const FREE_WIDGET_VIDEO_CAP = 1;

    /**
     * Free-tier build: VideoMate premium product page URL, used by the shared
     * upgrade banner/modal CTA.
     */
    const FREE_UPGRADE_URL = 'https://www.knowband.com/prestashop-videomate-module';

    /**
     * Constructor: Sets module properties and meta
     */
    public function __construct()
    {
        $this->name = 'kbvideomate';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'Knowband';
        $this->need_instance = 0;
        $this->module_key = '8b16f54258050a85d2287ff047ee5830';
        $this->bootstrap = true;

        parent::__construct();
        if (!isset($this->module) || $this->module === null) {
            $this->module = $this;
        }

        $this->displayName = $this->module->l('Knowband VideoMate', 'KbVideoMate');
        $this->description = $this->module->l('Upload and manage product videos with easy mapping to products', 'KbVideoMate');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * Install the module, create DB tables, register hooks, and install admin tabs
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->installDb()
            && Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE', 'large_default')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('displayAfterProductThumbs')
            && $this->registerHook('displayCategoryHeader')
            && $this->registerHook('displayManufacturerHeader')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayRightColumn')
            && $this->installTab();
    }

    /**
     * Uninstall the module and remove admin tabs
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallTab()
            && parent::uninstall();
    }

    /**
     * Create required database tables for the module
     *
     * - kbvideomate_library: Stores uploaded video information
     * - kbvideomate: Maps videos to products/entities with widget settings
     *
     * @return bool True if all tables are created successfully
     */
    public function installDb()
    {
        // SQL to create the video library table
        $sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'kbvideomate_library` (
            `id_kbvideomate_library` int(11) NOT NULL AUTO_INCREMENT, -- Primary key, auto-increment
            `alias_name` varchar(255) NOT NULL,                      -- Video alias or title
            `video_url` varchar(255) NOT NULL,                       -- Video URL or path
            `status` tinyint(1) NOT NULL DEFAULT 1,                  -- Status (enabled/disabled)
            `product_id` int(11) DEFAULT NULL,                       -- Selected product ID
            `category_id` int(11) DEFAULT NULL,                     -- Selected category ID
            `date_add` datetime NOT NULL,                            -- Creation date
            `date_upd` datetime NOT NULL,                            -- Last update date
            PRIMARY KEY (`id_kbvideomate_library`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';

        // SQL to create the video mapping table
        $sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'kbvideomate` (
            `id_kbvideomate` int(11) NOT NULL AUTO_INCREMENT,        -- Primary key, auto-increment
            `id_kbvideomate_library` varchar(255) NOT NULL,          -- Reference to video library
            `page` varchar(32) DEFAULT NULL,                         -- Page type (product/category/etc)
            `entity_ids` varchar(255) DEFAULT NULL,                  -- Comma-separated entity IDs
            `widget_type` varchar(32) DEFAULT NULL,                  -- Widget type (carousel, grid, etc)
            `max_visible` int(11) DEFAULT NULL,                      -- Max visible videos
            `active` tinyint(1) NOT NULL DEFAULT 1,                  -- Status (enabled/disabled)
            `date_add` datetime NOT NULL,                            -- Creation date
            `date_upd` datetime NOT NULL,                            -- Last update date
            `floating_position` ENUM(\'left\',\'right\') NOT NULL DEFAULT \'right\', -- Floating widget position
            `floating_remove_on_close` TINYINT(1) NOT NULL DEFAULT 0, -- Remove widget on close
            `carousel_autoplay_duration` INT DEFAULT NULL,           -- Carousel autoplay duration
            `video_as_main_image` TINYINT(1) NOT NULL DEFAULT 0,      -- Set video as main image in product gallery. | 10-02-2026
            PRIMARY KEY (`id_kbvideomate`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';

        // SQL to create the lang table for widget_heading
        $sql3 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'kbvideomate_lang` (
            `id_kbvideomate` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL DEFAULT 1,
            `widget_heading` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id_kbvideomate`,`id_lang`,`id_shop`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';

        /*
        * Create AI video queue table to store AI-generated video tasks.
	* @modifier Himanshu Vishwakarma
        * @date 11-02-2026
        */
        $sql4 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'kbvideomate_ai_video` (
            `id_kbvideomate_ai_video` int(11) NOT NULL AUTO_INCREMENT,
            `id_product` int(11) NOT NULL,
            `use_product_image` tinyint(1) NOT NULL DEFAULT 1,
            `use_product_description` tinyint(1) NOT NULL DEFAULT 1,
            `use_product_name` tinyint(1) NOT NULL DEFAULT 1,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            `queue_status` ENUM(\'pending\',\'processing\',\'completed\',\'failed\') NOT NULL DEFAULT \'pending\',
            `custom_prompt` TEXT DEFAULT NULL,
            `video_duration` int(11) NOT NULL DEFAULT 8,
            `video_url` varchar(255) DEFAULT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_kbvideomate_ai_video`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';

        // Execute all queries
        $result = Db::getInstance()->execute($sql1)
            && Db::getInstance()->execute($sql2)
            && Db::getInstance()->execute($sql3)
            && Db::getInstance()->execute($sql4);
        
        // Add product_id and category_id columns if they don't exist (for existing installations)
        /*
        * Add product_id and category_id columns to existing tables
        * 20-12-2024
        */
        $tableName = _DB_PREFIX_.'kbvideomate_library';
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `'.$tableName.'`');
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('product_id', $columnNames)) {
            Db::getInstance()->execute('ALTER TABLE `'.$tableName.'` ADD COLUMN `product_id` int(11) DEFAULT NULL AFTER `status`');
        }
        
        if (!in_array('category_id', $columnNames)) {
            Db::getInstance()->execute('ALTER TABLE `'.$tableName.'` ADD COLUMN `category_id` int(11) DEFAULT NULL AFTER `product_id`');
        }
        
        /*
        * Add redirection_type and redirection_url columns to existing tables
        * 20-12-2024
        */
        if (!in_array('redirection_type', $columnNames)) {
            Db::getInstance()->execute('ALTER TABLE `'.$tableName.'` ADD COLUMN `redirection_type` int(11) DEFAULT NULL AFTER `category_id`');
        }
        
        if (!in_array('redirection_url', $columnNames)) {
            Db::getInstance()->execute('ALTER TABLE `'.$tableName.'` ADD COLUMN `redirection_url` varchar(255) DEFAULT NULL AFTER `redirection_type`');
        }

        /* Add video_as_main_image column to kbvideomate for Product Video widget.
	* @modifier Himanshu Vishwakarma
        * @date 10-02-2026
	*/
        $kbvideomateCols = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.'kbvideomate`');
        $kbvideomateColNames = $kbvideomateCols ? array_column($kbvideomateCols, 'Field') : [];
        if (!in_array('video_as_main_image', $kbvideomateColNames)) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'kbvideomate` ADD COLUMN `video_as_main_image` TINYINT(1) NOT NULL DEFAULT 0');
        }

        /*
         * Add alias_name to AI video for widget display (pv_[product_name]); add id_kbvideomate_ai_video to library to link AI-originated rows.
         * @modifier Himanshu Vishwakarma
         * @date 10-02-2026
         */
        $aiVideoCols = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.'kbvideomate_ai_video`');
        $aiVideoColNames = $aiVideoCols ? array_column($aiVideoCols, 'Field') : [];
        if (!in_array('alias_name', $aiVideoColNames)) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'kbvideomate_ai_video` ADD COLUMN `alias_name` varchar(255) DEFAULT NULL AFTER `video_url`');
        }
        if (!in_array('error_message', $aiVideoColNames)) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'kbvideomate_ai_video` ADD COLUMN `error_message` varchar(1000) DEFAULT NULL AFTER `alias_name`');
        }
        if (!in_array('id_kbvideomate_ai_video', $columnNames)) {
            Db::getInstance()->execute('ALTER TABLE `'.$tableName.'` ADD COLUMN `id_kbvideomate_ai_video` int(11) DEFAULT NULL AFTER `redirection_url`');
        }

        return $result;
    }

    /**
     * Install admin tabs for the module
     *
     * - Creates a parent tab 'VideoMate' under the 'SELL' section
     * - Adds sub-tabs: General Setting, Video Library, Video Widget, and a hidden Ajax Handler
     * - Uses $this->module->l() for tab names and supports all languages
     *
     * @return bool True if all tabs are created successfully
     */
    public function installTab()
    {
        // 1. Create the parent tab under the 'SELL' section
        $parentTab = new Tab();
        $parentTab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $parentTab->name[$lang['id_lang']] = $this->module->l('VideoMate', 'KbVideoMate');
        }
        $parentTab->class_name = self::PARENT_TAB_CLASS;
        $parentTab->module = $this->name;
        $parentTab->active = true;
        $parentTab->id_parent = (int)Tab::getIdFromClassName(self::SELL_CLASS_NAME); // Under 'SELL'
        $parentTab->icon = 'videocam';
        $parentTab->add();

        // 2. Get the ID of the parent tab
        $id_parent_tab = (int)Tab::getIdFromClassName(self::PARENT_TAB_CLASS);

        // 3. Define sub-tabs with their class names and labels
        $admin_menus = [
            [
                'name' => $this->module->l('General Setting', 'KbVideoMate'),
                'class_name' => 'AdminKbVideoMateGeneral',
                'active' => 1,
            ],
            [
                'name' => $this->module->l('Video Library', 'KbVideoMate'),
                'class_name' => 'AdminKbVideoMateLibrary',
                'active' => 1,
            ],
            [
                'name' => $this->module->l('AI Video Library', 'KbVideoMate'),
                'class_name' => 'AdminKbVideoMateAiLibrary',
                'active' => 1,
            ],
            [
                'name' => $this->module->l('Video Widget', 'KbVideoMate'),
                'class_name' => 'AdminKbVideoMate',
                'active' => 1,
            ],
            [
                'name' => $this->module->l('Ajax Handler', 'KbVideoMate'),
                'class_name' => 'AdminKbVideoMateAjax',
                'active' => 1,
                'hidden' => 1, // Hide from menu but keep accessible
            ],
        ];

        // 4. Create sub-tabs under the parent tab for all languages
        foreach ($admin_menus as $menu) {
            $tab = new Tab();
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $menu['name'];
            }
            $tab->class_name = $menu['class_name'];
            $tab->module = $this->name;
            $tab->active = (bool)$menu['active'];
            $tab->id_parent = $id_parent_tab;
            if (isset($menu['hidden'])) {
                $tab->active = false; // Hide from menu
            }
            $tab->add();
        }
        return true;
    }

    /**
     * Remove admin tabs for the module
     *
     * - Removes all sub-tabs and the parent tab
     *
     * @return bool True if all tabs are removed successfully
     */
    public function uninstallTab()
    {
        // List of sub-tab class names to remove
        $subTabs = [
            'AdminKbVideoMate',
            'AdminKbVideoMateLibrary',
            'AdminKbVideoMateAiLibrary',
            'AdminKbVideoMateAjax',
            'AdminKbVideoMateGeneral',
        ];
        // Remove sub-tabs first
        foreach ($subTabs as $className) {
            $id_tab = (int)Tab::getIdFromClassName($className);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        // Remove parent tab
        $id_parent_tab = (int)Tab::getIdFromClassName(self::PARENT_TAB_CLASS);
        if ($id_parent_tab) {
            $parentTab = new Tab($id_parent_tab);
            $parentTab->delete();
        }
        return true;
    }

    /**
     * Free-tier build: count of widget mappings (kbvideomate rows), across every
     * widget_type - the table has no id_shop column, so this is already shop-wide by
     * construction (matches the Free-tier decision to force single default-shop
     * context rather than build new per-shop scoping).
     * @return int
     */
    public function kbvmCountWidgetMappings()
    {
        return (int)Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `'._DB_PREFIX_.'kbvideomate`'
        );
    }

    /**
     * Free-tier build: true once the shop has reached the lifetime widget-mapping cap -
     * no further widget mappings may be created until upgrade.
     * @return bool
     */
    public function kbvmHasReachedWidgetCap()
    {
        return $this->kbvmCountWidgetMappings() >= self::FREE_WIDGET_CAP;
    }

    /**
     * Free-tier build: count of video library entries, excluding AI-generated rows
     * (which cannot be created in the Free build) - matches the filter already used
     * by AdminKbVideoMateLibraryController's list.
     * @return int
     */
    public function kbvmCountLibraryRows()
    {
        return (int)Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `'._DB_PREFIX_.'kbvideomate_library`
            WHERE (id_kbvideomate_ai_video IS NULL OR id_kbvideomate_ai_video = 0)'
        );
    }

    /**
     * Free-tier build: true once the shop has reached the lifetime video-library cap -
     * no further videos may be added until upgrade.
     * @return bool
     */
    public function kbvmHasReachedLibraryCap()
    {
        return $this->kbvmCountLibraryRows() >= self::FREE_LIBRARY_CAP;
    }

    /**
     * Free-tier build: shared banner + upgrade modal, fetched at the top of every
     * admin controller's list/form output. Also enqueues the shared CSS/JS - callers
     * do not need to addCSS/addJS the free-tier assets separately.
     * @return string
     */
    public function renderFreeTierNotice()
    {
        if (isset($this->context->controller)) {
            $this->context->controller->addCSS($this->_path.'views/css/kbvideomate-free-tier.css');
            $this->context->controller->addJS($this->_path.'views/js/kbvideomate-upgrade-modal.js');
        }
        $this->context->smarty->assign([
            'kbvm_upgrade_url' => self::FREE_UPGRADE_URL,
        ]);
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/_kbvideomate_free_notice.tpl');
    }

    /**
     * Display the General Setting form in the module configuration (admin)
     *
     * - Uses HelperForm to render the form
     * - Includes a switch for enabling/disabling the module on the frontend
     * - Saves the setting to PrestaShop configuration and shows a confirmation message
     * - Uses $this->module->l() for all labels and messages
     *
     * @return string HTML content for the configuration page
     */
    public function getContent()
    {
        $output = '';

        /*
        * Include AI video settings JavaScript for dynamic UI and client-side validation.
        * @modifier Himanshu Vishwakarma
        * @date 10-02-2026
        */
        if (isset($this->context->controller)) {
            $this->context->controller->addJS($this->_path . 'views/js/ai-video-settings.js');
            $this->context->controller->addCSS($this->_path . 'views/css/cron-section.css');
        }

        if (Tools::isSubmit('submitKbVideoMateGeneral')) {
            $enabled = (int)Tools::getValue('KBVIDEOMATE_ENABLED', 0);

            /*
            * Free-tier build: AI Video Generation is a premium feature. The toggle and
            * every AI field stay visible + interactive in the UI (native switch,
            * readonly text/selects) but are force-reset to their safe defaults below
            * regardless of what was POSTed - no validation of platform/model/API key
            * here, since that would block saving on a toggle that can still be
            * flipped to Yes but never actually takes effect.
            */
            Configuration::updateValue('KBVIDEOMATE_ENABLED', $enabled);
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_ENABLED', 0);
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_PLATFORM', '');
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_MODEL', '');
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_API_KEY_GEMINI', '');
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_API_KEY_OPENAI', '');
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_API_KEY', '');
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_CRON_BATCH', 1);
            Configuration::updateValue('KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE', 'large_default');

            $output .= $this->displayConfirmation($this->module->l('Settings updated successfully.', 'KbVideoMate'));
        }

        $usePostedValues = Tools::isSubmit('submitKbVideoMateGeneral');
        $enabled = $usePostedValues ? (int)Tools::getValue('KBVIDEOMATE_ENABLED', 0) : (int)Configuration::get('KBVIDEOMATE_ENABLED');
        /*
        * Free-tier build: AI fields always reflect the freshly force-reset config
        * (Configuration::get(), never Tools::getValue()) - otherwise the form would
        * render the admin's just-submitted POST values immediately after a save that
        * actually persisted the safe defaults, which is misleading.
        */
        $aiVideoEnabled = (int)Configuration::get('KBVIDEOMATE_AI_VIDEO_ENABLED');
        $aiPlatform = (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_PLATFORM');
        $aiModel = (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_MODEL');
        $aiApiKeyGemini = (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_API_KEY_GEMINI');
        $aiApiKeyOpenAi = (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_API_KEY_OPENAI');
        $aiCronBatch = (int)Configuration::get('KBVIDEOMATE_AI_VIDEO_CRON_BATCH') ?: 1;
        $aiImageType = (string)Configuration::get('KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE') ?: 'large_default';

        // Build the HelperForm
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitKbVideoMateGeneral';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(
                'KBVIDEOMATE_ENABLED' => $enabled,
                'KBVIDEOMATE_AI_VIDEO_ENABLED' => $aiVideoEnabled,
                'KBVIDEOMATE_AI_VIDEO_PLATFORM' => $aiPlatform,
                'KBVIDEOMATE_AI_VIDEO_MODEL' => $aiModel,
                'KBVIDEOMATE_AI_VIDEO_API_KEY_GEMINI' => $aiApiKeyGemini,
                'KBVIDEOMATE_AI_VIDEO_API_KEY_OPENAI' => $aiApiKeyOpenAi,
                'KBVIDEOMATE_AI_VIDEO_CRON_BATCH' => $aiCronBatch,
                'KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE' => $aiImageType,
            ),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        // Define the form fields
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('General Setting', 'KbVideoMate'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Enable Product Video Module on Frontend', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_ENABLED',
                        'desc' => $this->module->l('Enable or disable the product video module for your shop frontend.', 'KbVideoMate'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled', 'KbVideoMate'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled', 'KbVideoMate'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Enable AI Video Generation', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_ENABLED',
                        // Free-tier build: AI Video Generation is premium - see the
                        // inline intercept script appended after generateForm() below,
                        // since native PS switch radio pairs have no per-field 'class'
                        // hook and must be gated by their fixed generated ids instead.
                        'desc' => $this->module->l('Enable or disable automatic video generation for products using AI platforms. This is a premium feature.', 'KbVideoMate'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'ai_video_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled', 'KbVideoMate'),
                            ),
                            array(
                                'id' => 'ai_video_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled', 'KbVideoMate'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('AI Platform for Video Generation', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_PLATFORM',
                        'options' => array(
                            'query' => array(
                                array('id' => 'gemini', 'name' => $this->module->l('Gemini', 'KbVideoMate')),
                                array('id' => 'chatgpt', 'name' => $this->module->l('ChatGPT', 'KbVideoMate')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'class' => 'kbvm-locked-toggle',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('AI Video Model', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_MODEL',
                        'options' => array(
                            'query' => array(
                                array('id' => 'models/veo-2.0-generate-001', 'name' => 'Gemini Veo 2.0'),
                                array('id' => 'models/veo-3.0-generate-001', 'name' => 'Gemini Veo 3.0'),
                                array('id' => 'models/veo-3.0-fast-generate-001', 'name' => 'Gemini Veo 3.0 Fast'),
                                array('id' => 'models/veo-3.1-generate-preview', 'name' => 'Gemini Veo 3.1 Preview'),
                                array('id' => 'models/veo-3.1-fast-generate-preview', 'name' => 'Gemini Veo 3.1 Fast Preview'),
                                array('id' => 'sora-2', 'name' => 'OpenAI Sora 2'),
                                array('id' => 'sora-2-pro', 'name' => 'OpenAI Sora 2 Pro'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'class' => 'kbvm-locked-toggle',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Gemini API Key', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_API_KEY_GEMINI',
                        'desc' => $this->module->l('Enter the API key used to call the Gemini Veo video generation API.', 'KbVideoMate'),
                        'readonly' => true,
                        'class' => 'kbvm-locked-field',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('OpenAI API Key', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_API_KEY_OPENAI',
                        'desc' => $this->module->l('Enter the API key used to call the OpenAI Sora video generation API.', 'KbVideoMate'),
                        'readonly' => true,
                        'class' => 'kbvm-locked-field',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Number of video generation per cron', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_CRON_BATCH',
                        'desc' => $this->module->l('Enter the number of videos to generate per cron execution (numeric value only).', 'KbVideoMate'),
                        'readonly' => true,
                        'class' => 'kbvm-locked-field',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Image type', 'KbVideoMate'),
                        'name' => 'KBVIDEOMATE_AI_VIDEO_IMAGE_TYPE',
                        'desc' => $this->module->l('Select the PrestaShop image type (e.g. cart_default, large_default) for AI-generated product videos.', 'KbVideoMate'),
                        'options' => array(
                            'query' => $this->getImageTypesForSelect(),
                            'id' => 'name',
                            'name' => 'name',
                        ),
                        'class' => 'kbvm-locked-toggle',
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Save', 'KbVideoMate'),
                ),
            ),
        );

        // Generate the General Setting form HTML
        $output .= $helper->generateForm(array($fields_form));

        $output .= $this->renderAiApiInfo();

        $output .= $this->renderCronUrlBlock();

        /*
        * Free-tier build: banner + shared upgrade modal at the top of the page, plus
        * an inline intercept for the AI enable switch's fixed generated ids (native PS
        * switch radio pairs have no per-field 'class' hook).
        */
        $output = $this->renderFreeTierNotice()
            . '<script type="text/javascript">if (window.kbvmInterceptGatedChange) { window.kbvmInterceptGatedChange("#ai_video_on, #ai_video_off"); }</script>'
            . $output;

        return $output;
    }

    /*
    * Returns PrestaShop image types for dropdown from Design > Image Settings.
    * No empty option; only available image types. Default large_default set on install.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026
    */
    private function getImageTypesForSelect(): array
    {
        $sql = 'SELECT `name` FROM `' . _DB_PREFIX_ . 'image_type` ORDER BY `name` ASC';
        $rows = Db::getInstance()->executeS($sql);

        return is_array($rows) ? $rows : [];
    }

    /*
    * Renders AI API key setup and billing info panel for General Settings.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026
    */
    private function renderAiApiInfo(): string
    {
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/_ai_api_info.tpl');
    }

    /*
    * Renders the cron URL section via Smarty template; no HTML in controller.
    * Free-tier build: the "Open URL" button is a genuinely disabled <button> (not a
    * working link) - the front cron controller/generator engine that this URL would
    * have pointed to no longer exist in this build, this block is purely informational
    * reference for the premium version.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026
    */
    private function renderCronUrlBlock(): string
    {
        $cronUrl = $this->context->link->getModuleLink('kbvideomate', 'cron', [], true);

        $this->context->smarty->assign([
            'section_title' => $this->module->l('Cron URL for AI Video Generation', 'KbVideoMate'),
            'cron_url' => $cronUrl,
            'cron_help_text' => $this->module->l('Call this URL periodically (e.g. every 5–10 minutes) to process pending AI video generation. Up to the configured number of videos per cron run are generated per request.', 'KbVideoMate'),
            'cron_open_btn' => $this->module->l('Open URL', 'KbVideoMate'),
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/_cron_url_block.tpl');
    }

    /**
     * Hook: displayHeader
     * Adds CSS/JS assets to the frontend header if needed
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/kbvideomate.css');
        $this->context->controller->addJS($this->_path.'views/js/kbvideomate.js');
        $this->context->controller->addCSS($this->_path . 'views/css/kbproductvideo.css');
        
        // Add JavaScript
        $this->context->controller->addJS($this->_path . 'views/js/kbproductvideo.js');
        return '';
    }


    public function hookDisplayAfterProductThumbs($params)
    {
        /*
        * Respect KBVIDEOMATE_ENABLED flag so product video thumbnails are hidden
        * when the module is disabled from General Settings.
        * 25-02-2026
        */
        if (!(int)Configuration::get('KBVIDEOMATE_ENABLED')) {
            return '';
        }

        $id_product = (int)$params['product']['id_product'];
        $sql = 'SELECT v.*, l.video_url, l.alias_name
                FROM `'._DB_PREFIX_.'kbvideomate` v
                INNER JOIN `'._DB_PREFIX_.'kbvideomate_library` l ON FIND_IN_SET(l.id_kbvideomate_library, v.id_kbvideomate_library)
                LEFT JOIN `'._DB_PREFIX_.'kbvideomate_ai_video` ai ON ai.id_kbvideomate_ai_video = l.id_kbvideomate_ai_video
                WHERE FIND_IN_SET('.(int)$id_product.', v.entity_ids)
                AND v.active = 1
                AND l.status = 1
                AND (l.id_kbvideomate_ai_video IS NULL OR l.id_kbvideomate_ai_video = 0 OR (ai.id_kbvideomate_ai_video IS NOT NULL AND ai.active = 1))
                AND v.page = "product"';
        $mappings = Db::getInstance()->executeS($sql);
        if (empty($mappings)) {
            return '';
        }
        $single_video = [];
        $video_as_main_image = false;
        foreach ($mappings as $mapping) {
            if ($mapping['widget_type'] === 'product') {
                $single_video[] = [
                    'video_url' => $mapping['video_url'],
                    'alias_name' => $mapping['alias_name'],
                ];
                /* Use video as main image when widget has video_as_main_image enabled.
		* @modifier Himanshu Vishwakarma
	        * @date 10-02-2026 
		*/
                if (!empty($mapping['video_as_main_image'])) {
                    $video_as_main_image = true;
                }
            }
        }

        // Prepare video content
        $this->context->smarty->assign([
            'videos' => $single_video,
            'video_as_main_image' => $video_as_main_image,
            'module_dir' => $this->_path,
            'product' => $params['product'],
            'link' => $this->context->link
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/product_video_thumbs.tpl');
    }

    /**
     * Hook: displayFooterProduct
     * Render video widgets in the product footer
     * @param array $params
     * @return string
     */
    public function hookDisplayFooterProduct($params)
    {
        if (!(int)Configuration::get('KBVIDEOMATE_ENABLED')) { return ''; }

        $id_product = (int)$params['product']['id_product'];
        $sql = 'SELECT v.*, l.video_url, l.alias_name
                FROM `'._DB_PREFIX_.'kbvideomate` v
                INNER JOIN `'._DB_PREFIX_.'kbvideomate_library` l ON FIND_IN_SET(l.id_kbvideomate_library, v.id_kbvideomate_library)
                LEFT JOIN `'._DB_PREFIX_.'kbvideomate_ai_video` ai ON ai.id_kbvideomate_ai_video = l.id_kbvideomate_ai_video
                WHERE FIND_IN_SET('.(int)$id_product.', v.entity_ids)
                AND v.active = 1
                AND l.status = 1
                AND (l.id_kbvideomate_ai_video IS NULL OR l.id_kbvideomate_ai_video = 0 OR (ai.id_kbvideomate_ai_video IS NOT NULL AND ai.active = 1))
                AND v.page = "product"';
        $mappings = Db::getInstance()->executeS($sql);
        if (empty($mappings)) {
            return '';
        }
        $single_video = [];
        foreach ($mappings as $mapping) {
            if ($mapping['widget_type'] === 'single') {
                $single_video[] = [
                    'video_url' => $mapping['video_url'],
                    'alias_name' => $mapping['alias_name'],
                ];
                // Only show the first single video found
                break;
            }
        }
        if (!empty($single_video)) {
            $this->context->smarty->assign([
                'single_video' => $single_video,
                'module_dir' => $this->_path,
                'link' => $this->context->link
            ]);
            return $this->display(__FILE__, 'views/templates/hook/product-videos-single.tpl');
        }
        return '';
    }

    /**
     * Hook: displayProductAdditionalInfo
     * Render video widgets on the product page
     * @param array $params
     * @return string
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int)Configuration::get('KBVIDEOMATE_ENABLED')) { return ''; }

        $id_product = (int)$params['product']['id_product'];
        // Get all active mappings for this product
        $sql = 'SELECT v.*, l.video_url, l.alias_name
                FROM `'._DB_PREFIX_.'kbvideomate` v
                INNER JOIN `'._DB_PREFIX_.'kbvideomate_library` l ON FIND_IN_SET(l.id_kbvideomate_library, v.id_kbvideomate_library)
                LEFT JOIN `'._DB_PREFIX_.'kbvideomate_ai_video` ai ON ai.id_kbvideomate_ai_video = l.id_kbvideomate_ai_video
                WHERE FIND_IN_SET('.(int)$id_product.', v.entity_ids)
                AND v.active = 1
                AND l.status = 1
                AND (l.id_kbvideomate_ai_video IS NULL OR l.id_kbvideomate_ai_video = 0 OR (ai.id_kbvideomate_ai_video IS NOT NULL AND ai.active = 1))
                AND v.page = "product"';
        $mappings = Db::getInstance()->executeS($sql);
        if (empty($mappings)) {
            return '';
        }
        $output = '';
        $floating_videos = [];
        /*
        * Free-tier build: Popup Position Left is premium - always render on the right
        * regardless of any older left value still stored in floating_position.
        */
        $floating_position = 'right';
        $floating_remove_on_close = 0;
        foreach ($mappings as $mapping) {
            if ($mapping['widget_type'] === 'floating') {
                $floating_videos[] = [
                    'video_url' => $mapping['video_url'],
                    'alias_name' => $mapping['alias_name'],
                ];
                if (isset($mapping['floating_remove_on_close'])) {
                    $floating_remove_on_close = (int)$mapping['floating_remove_on_close'];
                }
            }
        }
        // Display floating widget if exists
        if (!empty($floating_videos)) {
            $this->context->smarty->assign([
                'floating_videos' => $floating_videos,
                'floating_position' => $floating_position,
                'floating_remove_on_close' => $floating_remove_on_close,
                'module_dir' => $this->_path
            ]);
            $output .= $this->display(__FILE__, 'views/templates/hook/product-videos.tpl');
        }
        return $output;
    }

    /**
     * Hook: displayCategoryHeader
     * Render video widgets on the category page
     *
     * Free-tier build: Category-page widgets are a premium feature - return
     * immediately rather than calling the shared renderer, so it never queries or
     * renders regardless of what mappings exist in the DB (e.g. a shop downgraded
     * from Paid keeps its category-page mappings intact for a lossless re-upgrade,
     * they simply don't render while running Free).
     * @param array $params
     * @return string
     */
    public function hookDisplayCategoryHeader($params)
    {
        return '';
    }

    /**
     * Hook: displayManufacturerHeader
     * Render video widgets on the manufacturer page
     *
     * Free-tier build: Manufacturer-page widgets are a premium feature (same bucket
     * as Category/Home/Best Seller/New Products) - return immediately.
     * @param array $params
     * @return string
     */
    public function hookDisplayManufacturerHeader($params)
    {
        return '';
    }

    /**
     * Hook: displayHome
     * Render video widgets on the home page
     *
     * Free-tier build: Homepage widgets are a premium feature - return immediately.
     * @param array $params
     * @return string
     */
    public function hookDisplayHome($params)
    {
        return '';
    }

    /**
     * Hook: displayLeftColumn
     * Render video widgets in the left column
     *
     * Free-tier build: same premium bucket as Category/Home - return immediately.
     * @param array $params
     * @return string
     */
    public function hookDisplayLeftColumn($params)
    {
        return '';
    }

    /**
     * Hook: displayRightColumn
     * Render video widgets in the right column
     *
     * Free-tier build: same premium bucket as Category/Home - return immediately.
     * @param array $params
     * @return string
     */
    public function hookDisplayRightColumn($params)
    {
        return '';
    }
}