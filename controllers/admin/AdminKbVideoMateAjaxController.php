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

/**
 * AdminKbVideoMateAjaxController
 * Handles AJAX entity search for kbvideomate widget configuration
 */
class AdminKbVideoMateAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        header('Content-Type: application/json');
    }
    
    public function initContent()
    {
        parent::initContent();
        $this->ajaxProcess();
    }
    /**
     * Main AJAX entry point for entity search
     */
    public function ajaxProcess()
    {
        try {
            $type = Tools::getValue('type');
            $q = Tools::getValue('q');
            $items = [];
            $context = Context::getContext();
            $langId = (int)$context->language->id;
            $shopId = (int)$context->shop->id;

            switch ($type) {
                case 'product':
                    $sql = 'SELECT DISTINCT p.id_product, pl.name 
                           FROM '._DB_PREFIX_.'product p
                           INNER JOIN '._DB_PREFIX_.'product_lang pl ON (p.id_product = pl.id_product)
                           INNER JOIN '._DB_PREFIX_.'product_shop ps ON (p.id_product = ps.id_product)
                           WHERE pl.id_lang = '.(int)$langId.'
                           AND ps.id_shop = '.(int)$shopId.'
                           AND pl.name LIKE "%'.pSQL($q).'%"
                           AND p.active = 1
                           ORDER BY pl.name ASC
                           LIMIT 20';
                    $results = Db::getInstance()->executeS($sql);
                    foreach ($results as $row) {
                        $items[] = ['id' => $row['id_product'], 'text' => $row['name']];
                    }
                    break;

                case 'category':
                    $sql = 'SELECT DISTINCT c.id_category, cl.name 
                           FROM '._DB_PREFIX_.'category c
                           INNER JOIN '._DB_PREFIX_.'category_lang cl ON (c.id_category = cl.id_category)
                           INNER JOIN '._DB_PREFIX_.'category_shop cs ON (c.id_category = cs.id_category)
                           WHERE cl.id_lang = '.(int)$langId.'
                           AND cs.id_shop = '.(int)$shopId.'
                           AND cl.name LIKE "%'.pSQL($q).'%"
                           AND c.active = 1
                           ORDER BY cl.name ASC
                           LIMIT 20';
                    $results = Db::getInstance()->executeS($sql);
                    foreach ($results as $row) {
                        $items[] = ['id' => $row['id_category'], 'text' => $row['name']];
                    }
                    break;

                case 'cms':
                    $sql = 'SELECT DISTINCT c.id_cms, cl.meta_title 
                           FROM '._DB_PREFIX_.'cms c
                           INNER JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms)
                           INNER JOIN '._DB_PREFIX_.'cms_shop cs ON (c.id_cms = cs.id_cms)
                           WHERE cl.id_lang = '.(int)$langId.'
                           AND cs.id_shop = '.(int)$shopId.'
                           AND cl.meta_title LIKE "%'.pSQL($q).'%"
                           AND c.active = 1
                           ORDER BY cl.meta_title ASC
                           LIMIT 20';
                    $results = Db::getInstance()->executeS($sql);
                    foreach ($results as $row) {
                        $items[] = ['id' => $row['id_cms'], 'text' => $row['meta_title']];
                    }
                    break;

                case 'manufacturer':
                    $sql = 'SELECT DISTINCT m.id_manufacturer, m.name 
                           FROM '._DB_PREFIX_.'manufacturer m
                           INNER JOIN '._DB_PREFIX_.'manufacturer_shop ms ON (m.id_manufacturer = ms.id_manufacturer)
                           WHERE ms.id_shop = '.(int)$shopId.'
                           AND m.name LIKE "%'.pSQL($q).'%"
                           AND m.active = 1
                           ORDER BY m.name ASC
                           LIMIT 20';
                    $results = Db::getInstance()->executeS($sql);
                    foreach ($results as $row) {
                        $items[] = ['id' => $row['id_manufacturer'], 'text' => $row['name']];
                    }
                    break;

                case 'supplier':
                    $sql = 'SELECT DISTINCT s.id_supplier, s.name 
                           FROM '._DB_PREFIX_.'supplier s
                           INNER JOIN '._DB_PREFIX_.'supplier_shop ss ON (s.id_supplier = ss.id_supplier)
                           WHERE ss.id_shop = '.(int)$shopId.'
                           AND s.name LIKE "%'.pSQL($q).'%"
                           AND s.active = 1
                           ORDER BY s.name ASC
                           LIMIT 20';
                    $results = Db::getInstance()->executeS($sql);
                    foreach ($results as $row) {
                        $items[] = ['id' => $row['id_supplier'], 'text' => $row['name']];
                    }
                    break;
            }

            die(json_encode(['items' => $items]));
        } catch (Exception $e) {
            http_response_code(500);
            die(json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]));
        }
    }
} 