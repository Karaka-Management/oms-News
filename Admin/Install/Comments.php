<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\News\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\News\Admin\Install;

use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\DataStorage\Database\Schema\Builder;

/**
 * Comments class.
 *
 * @package Modules\News\Admin\Install
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class Comments
{
    /**
     * Install comment relation
     *
     * @param string       $path   Module path
     * @param DatabasePool $dbPool Database pool for database interaction
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(string $path, DatabasePool $dbPool) : void
    {
        $builder = new Builder($dbPool->get('schema'));
        $builder->alterTable('news')
            ->addConstraint('news_comment_list', 'comment_list', 'comment_list_id');

        $mapper = \file_get_contents(__DIR__ . '/../../Models/NewsArticleMapper.php');
        $mapper = \str_replace([
            '// @Module Comments ',
            '/* @Module Comments ',
            ' @Module Comments */'
            ], '', $mapper);
        \file_put_contents(__DIR__ . '/../../Models/NewsArticleMapper.php', $mapper);

        Autoloader::invalidate(__DIR__ . '/../../Models/NewsArticleMapper.php');
    }
}
