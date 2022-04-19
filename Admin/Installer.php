<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\News\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\News\Admin;

use phpOMS\Module\InstallerAbstract;

/**
 * Installer class.
 *
 * @package Modules\News\Admin
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;
}
