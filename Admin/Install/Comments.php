<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\News\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
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
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Comments
{
    /**
     * Install comment relation
     *
     * @param ApplicationAbstract $app  Application
     * @param string              $path Module path
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(ApplicationAbstract $app, string $path) : void
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
