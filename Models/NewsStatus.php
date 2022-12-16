<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * News type status.
 *
 * @package Modules\News\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class NewsStatus extends Enum
{
    public const VISIBLE = 0;

    public const DRAFT = 1;
}
