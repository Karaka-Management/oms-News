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

use Modules\News\Models\NullNewsArticle;

/**
 * @internal
 */
final class NullNewsArticleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\News\Models\NullNewsArticle
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\News\Models\NewsArticle', new NullNewsArticle());
    }

    /**
     * @covers Modules\News\Models\NullNewsArticle
     * @group module
     */
    public function testId() : void
    {
        $null = new NullNewsArticle(2);
        self::assertEquals(2, $null->getId());
    }
}
