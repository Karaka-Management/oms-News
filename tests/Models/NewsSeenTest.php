<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\tests\Models;

use Modules\News\Models\NewsSeen;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\News\Models\NewsSeen::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\News\tests\Models\NewsSeenTest: News article')]
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->seen->id);
        self::assertEquals(0, $this->seen->seenBy);
        self::assertInstanceOf('\DateTime', $this->seen->seenAt);
    }
}
