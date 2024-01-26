<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Comments\Models\CommentListMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * News mapper class.
 *
 * @package Modules\News\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of NewsArticle
 * @extends DataMapperFactory<T>
 */
final class NewsArticleMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'news_id'           => ['name' => 'news_id',           'type' => 'int',               'internal' => 'id'],
        'news_publish'      => ['name' => 'news_publish',      'type' => 'DateTime',          'internal' => 'publish'],
        'news_title'        => ['name' => 'news_title',        'type' => 'string',            'internal' => 'title'],
        'news_plain'        => ['name' => 'news_plain',        'type' => 'string',            'internal' => 'plain'],
        'news_content'      => ['name' => 'news_content',      'type' => 'string',            'internal' => 'content'],
        'news_lang'         => ['name' => 'news_lang',         'type' => 'string',            'internal' => 'language'],
        'news_status'       => ['name' => 'news_status',       'type' => 'int',               'internal' => 'status'],
        'news_type'         => ['name' => 'news_type',         'type' => 'int',               'internal' => 'type'],
        'news_featured'     => ['name' => 'news_featured',     'type' => 'bool',              'internal' => 'isFeatured'],
        'news_comment_list' => ['name' => 'news_comment_list', 'type' => 'int',               'internal' => 'comments'],
        'news_unit'         => ['name' => 'news_unit',   'type' => 'int',               'internal' => 'unit'],
        'news_app'          => ['name' => 'news_app',   'type' => 'int',               'internal' => 'app'],
        'news_created_at'   => ['name' => 'news_created_at',   'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'news_created_by'   => ['name' => 'news_created_by',   'type' => 'int',               'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'comments' => [
            'mapper'   => CommentListMapper::class,
            'external' => 'news_comment_list',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'news_created_by',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'tags' => [
            'mapper'   => TagMapper::class,
            'table'    => 'news_tag',
            'self'     => 'news_tag_dst',
            'external' => 'news_tag_src',
        ],
        'files' => [
            'mapper'   => MediaMapper::class,
            'table'    => 'news_media',
            'external' => 'news_media_dst',
            'self'     => 'news_media_src',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'news';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'news_id';
}
