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
 * Model class for kbvideomate widget mapping (kbvideomate table)
 */
class KbVideoMateWidget extends ObjectModel
{
    public $id_kbvideomate;
    public $id_kbvideomate_library;
    public $active;
    public $date_add;
    public $date_upd;
    public $page;
    public $entity_ids;
    public $widget_type;
    public $max_visible;
    public $floating_position;
    public $floating_remove_on_close;
    public $widget_heading;
    public $carousel_autoplay_duration;
    public $video_as_main_image;

    public static $definition = [
        'table' => 'kbvideomate',
        'primary' => 'id_kbvideomate',
        'multilang' => true,
        'fields' => [
            'id_kbvideomate_library' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'page' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32],
            'entity_ids' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255],
            'widget_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32],
            'max_visible' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'floating_position' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 8],
            'floating_remove_on_close' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
            'widget_heading' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255, 'lang' => true],
            'carousel_autoplay_duration' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'video_as_main_image' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
        ],
    ];
} 