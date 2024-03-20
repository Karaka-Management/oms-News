<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
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
     * @covers \Modules\News\Models\NullNewsSeen
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\News\Models\NewsSeen', new NullNewsSeen());
    }

    /**
     * @covers \Modules\News\Models\NullNewsSeen
     * @group module
     */
    public function testId() : void
    {
        $null = new NullNewsSeen(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers \Modules\News\Models\NullNewsSeen
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullNewsSeen(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
