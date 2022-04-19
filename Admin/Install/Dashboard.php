<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\News\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\News\Admin\Install;

use phpOMS\Application\ApplicationAbstract;

/**
 * Dashboard class.
 *
 * @package Modules\News\Admin\Install
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class Dashboard
{
    /**
     * Install dashboard providing
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
        \Modules\Dashboard\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Dashboard.install.json']);
    }
}
