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

/*
* ObjectModel for kbvideomate_ai_video table to track AI-generated video tasks.
* @modifier Himanshu Vishwakarma
* @date 11-02-2026
*/
class KbVideoMateAiVideo extends ObjectModel
{
    /** @var int */
    public $id_kbvideomate_ai_video;

    /** @var int */
    public $id_product;

    /** @var int */
    public $use_product_image;

    /** @var int */
    public $use_product_description;

    /** @var int */
    public $use_product_name;

    /** @var int */
    public $active;

    /** @var string */
    public $queue_status;

    /** @var string|null */
    public $custom_prompt;

    /** @var int */
    public $video_duration;

    /** @var string|null */
    public $video_url;

    /** @var string|null Alias for widget display, e.g. pv_[product_name]. | 10-02-2026 */
    public $alias_name;

    /** @var string|null API error message when queue_status is failed. | 10-02-2026 */
    public $error_message;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => 'kbvideomate_ai_video',
        'primary' => 'id_kbvideomate_ai_video',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'use_product_image' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'use_product_description' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'use_product_name' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'queue_status' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'custom_prompt' => ['type' => self::TYPE_STRING,'size' => 65000],
            'video_duration' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'video_url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
            'alias_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255],
            'error_message' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 1000],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}

