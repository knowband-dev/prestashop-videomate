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
 * AdminKbVideoMateGeneralController
 *
 * This controller is required for the "General Setting" admin tab.
 * It simply redirects to the module configuration page for kbvideomate.
 *
 * This avoids errors about a missing or invalid controller.
 *
 * @see kbvideomate.php tab registration
 */
class AdminKbVideoMateGeneralController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Redirect to the module configuration page
     */
    public function initContent()
    {
        // Build the config page URL
        $link = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => 'kbvideomate',
        ]);
        Tools::redirectAdmin($link);
    }
} 