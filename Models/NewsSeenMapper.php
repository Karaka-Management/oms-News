<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
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
use Modules\Comments\Models\CommentListMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * News mapper class.
 *
 * @package Modules\News\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 *
 * @todo Orange-Management/oms-News#???
 *  Too complicated select.
 *  I think the default getAll etc. is too complicated and has too many joins which are not really required.
 *  Check and fix!
 */
final class NewsSeenMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'news_seen_id'           => ['name' => 'news_seen_id',         'type' => 'int',      'internal' => 'id'],
        'news_seen_at'   => ['name' => 'news_seen_at', 'type' => 'DateTime', 'internal' => 'seenAt'],
        'news_seen_by'   => ['name' => 'news_seen_by', 'type' => 'int',      'internal' => 'seenBy'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'news_seen';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'news_seen_id';
}
