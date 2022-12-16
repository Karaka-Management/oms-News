<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\tests\Models;

use Modules\News\Models\NullNewsSeen;

/**
 * @internal
 */
final class NullNewsSeenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\News\Models\NullNewsSeen
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\News\Models\NewsSeen', new NullNewsSeen());
    }

    /**
     * @covers Modules\News\Models\NullNewsSeen
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullNewsSeen(2);
        self::assertEquals(2, $null->getId());
    }
}
