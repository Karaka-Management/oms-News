<?php
/**
 * Karaka
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

use Modules\News\Models\NewsSeen;

/**
 * @testdox Modules\News\tests\Models\NewsSeenTest: News article
 *
 * @internal
 */
final class NewsSeenTest extends \PHPUnit\Framework\TestCase
{
    protected NewsSeen $seen;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->seen = new NewsSeen();
    }

    /**
     * @covers Modules\News\Models\NewsSeen
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->seen->getId());
        self::assertEquals(0, $this->seen->seenBy);
        self::assertInstanceOf('\DateTime', $this->seen->seenAt);
    }
}
