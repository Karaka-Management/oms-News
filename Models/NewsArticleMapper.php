<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\News\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * News mapper class.
 *
 * @package Modules\News\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class NewsArticleMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'news_id'         => ['name' => 'news_id',         'type' => 'int',      'internal' => 'id'],
        'news_created_by' => ['name' => 'news_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'news_publish'    => ['name' => 'news_publish',    'type' => 'DateTime', 'internal' => 'publish'],
        'news_title'      => ['name' => 'news_title',      'type' => 'string',   'internal' => 'title'],
        'news_plain'      => ['name' => 'news_plain',      'type' => 'string',   'internal' => 'plain'],
        'news_content'    => ['name' => 'news_content',    'type' => 'string',   'internal' => 'content'],
        'news_lang'       => ['name' => 'news_lang',       'type' => 'string',   'internal' => 'language'],
        'news_status'     => ['name' => 'news_status',     'type' => 'int',      'internal' => 'status'],
        'news_type'       => ['name' => 'news_type',       'type' => 'int',      'internal' => 'type'],
        'news_featured'   => ['name' => 'news_featured',   'type' => 'bool',     'internal' => 'featured'],
        'news_created_at' => ['name' => 'news_created_at', 'type' => 'DateTime', 'internal' => 'createdAt', 'readonly' => true],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, self:string}>
     * @since 1.0.0
     */
    protected static array $belongsTo = [
        'createdBy' => [
            'mapper' => AccountMapper::class,
            'self'   => 'news_created_by',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    protected static array $hasMany = [
        'tags' => [
            'mapper' => TagMapper::class,
            'table'  => 'news_tag',
            'self'   => 'news_tag_src',
            'external' => 'news_tag_dst',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'news';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'news_id';
}
