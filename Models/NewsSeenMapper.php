<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * News mapper class.
 *
 * @package Modules\News\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of NewsSeen
 * @extends DataMapperFactory<T>
 */
final class NewsSeenMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'news_seen_id'   => ['name' => 'news_seen_id',   'type' => 'int',      'internal' => 'id'],
        'news_seen_at'   => ['name' => 'news_seen_at',   'type' => 'DateTime', 'internal' => 'seenAt'],
        'news_seen_news' => ['name' => 'news_seen_news', 'type' => 'int',      'internal' => 'news'],
        'news_seen_by'   => ['name' => 'news_seen_by',   'type' => 'int',      'internal' => 'seenBy'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'news_seen';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'news_seen_id';
}
