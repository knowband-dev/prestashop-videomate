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
 * KbVideoMateLibrary Model
 * Handles the video library records for kbvideomate
 */
class KbVideoMateLibrary extends ObjectModel
{
    /** @var int */
    public $id_kbvideomate_library;
    /** @var string */
    public $alias_name;
    /** @var string */
    public $video_url;
    /** @var string */
    public $date_add;
    /** @var string */
    public $date_upd;
    /** @var int */
    public $status; // 1 = enabled, 0 = disabled
    /** @var int */
    public $active; // Alias for status, used by PrestaShop for toggle
    /** @var int */
    public $product_id; // Selected product ID
    /** @var int */
    public $category_id; // Selected category ID (single selection)
    /** @var int */
    public $redirection_type; // Redirection type: 1=Product, 2=Category, 3=Custom URL
    /** @var string */
    public $redirection_url; // Custom redirection URL when redirection_type is 3

    /** @var int|null Links to AI video when this library row was created from AI generation.
    * @modifier Himanshu Vishwakarma
    * @date 10-02-2026 
    */
    public $id_kbvideomate_ai_video;

    /**
     * Table definition for ObjectModel
     */
    public static $definition = [
        'table' => 'kbvideomate_library',
        'primary' => 'id_kbvideomate_library',
        'fields' => [
            'alias_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'video_url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true, 'size' => 255],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
            'redirection_type' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'product_id' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'category_id' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'redirection_url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => false, 'size' => 255],
            'id_kbvideomate_ai_video' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Get all videos for select2 dropdown (id and alias_name).
     * When $onlyActive is true, excludes disabled Video Library items (status=0) and library rows
     * linked to disabled AI videos (AI Video Library active=0) so they do not appear in search/selection.
     *
     * @param bool $onlyActive Whether to return only active videos (excludes disabled library and disabled AI)
     * @return array
     */
    public static function getAllForSelect($onlyActive = true)
    {
        $prefix = _DB_PREFIX_;
        $sql = 'SELECT l.id_kbvideomate_library, l.alias_name FROM `'.$prefix.'kbvideomate_library` l ';
        if ($onlyActive) {
            $sql .= 'LEFT JOIN `'.$prefix.'kbvideomate_ai_video` ai ON ai.id_kbvideomate_ai_video = l.id_kbvideomate_ai_video ';
            $sql .= 'WHERE l.status = 1 ';
            $sql .= 'AND (l.id_kbvideomate_ai_video IS NULL OR l.id_kbvideomate_ai_video = 0 OR (ai.id_kbvideomate_ai_video IS NOT NULL AND ai.active = 1)) ';
        }
        $sql .= 'ORDER BY l.alias_name ASC';
        return Db::getInstance()->executeS($sql);
    }

    // Synchronize active <-> status for PrestaShop admin toggling
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        $this->active = $this->status;
    }

    public function save($null_values = false, $auto_date = true)
    {
        $this->status = $this->active;
        return parent::save($null_values, $auto_date);
    }
} 