<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Models;

/**
 * Null model
 *
 * @package Modules\News\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
class NewsSeen
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    public \DateTime $seenAt;

    public int $seenBy = 0;

    public int $news = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->seenAt = new \DateTime('now');
    }
}
