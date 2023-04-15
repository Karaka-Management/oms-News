<?php
/**
 * Karaka
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

use phpOMS\Stdlib\Base\Enum;

/**
 * News type enum.
 *
 * @package Modules\News\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class NewsType extends Enum
{
    public const ARTICLE = 1;

    public const LINK = 2;

    public const HEADLINE = 3;
}
