<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\News\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\News\Admin\Install;

use phpOMS\Application\ApplicationAbstract;
use phpOMS\Autoloader;
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
     * @param string              $path Module path
     * @param ApplicationAbstract $app  Application
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(string $path, ApplicationAbstract $app) : void
    {
        $builder = new Builder($app->dbPool->get('schema'));
        $builder->alterTable('news')
            ->addConstraint('news_comment_list', 'comments_list', 'comments_list_id')
            ->execute();

        $mapper = \file_get_contents(__DIR__ . '/../../Models/NewsArticleMapper.php');
        if ($mapper === false) {
            throw new \Exception('Couldn\'t parse mapper');
        }

        $mapper = \str_replace([
            '// @Module Comments ',
            '/* @Module Comments ',
            ' @Module Comments */',
            ], '', $mapper);
        \file_put_contents(__DIR__ . '/../../Models/NewsArticleMapper.php', $mapper);

        Autoloader::invalidate(__DIR__ . '/../../Models/NewsArticleMapper.php');
    }
}
