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

use Modules\Admin\Models\NullAccount;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use phpOMS\Localization\ISO639x1Enum;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\News\Models\NewsArticle::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\News\tests\Models\NewsArticleTest: News article')]
final class NewsArticleTest extends \PHPUnit\Framework\TestCase
{
    protected NewsArticle $news;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->news = new NewsArticle();
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model has the expected default values after initialization')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->news->id);
        self::assertEquals(0, $this->news->createdBy->id);
        self::assertEquals('', $this->news->title);
        self::assertEquals('', $this->news->content);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->news->createdAt->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->news->publish->format('Y-m-d'));
        self::assertFalse($this->news->isFeatured);
        self::assertEquals(ISO639x1Enum::_EN, $this->news->language);
        self::assertEquals(NewsStatus::DRAFT, $this->news->status);
        self::assertEquals(NewsType::ARTICLE, $this->news->type);
        self::assertEquals([], $this->news->tags);
        self::assertEquals('', $this->news->plain);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The creator can be correctly set and returned')]
    public function testCreatorInputOutput() : void
    {
        $this->news->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->news->createdBy->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The title can be correctly set and returned')]
    public function testTitleInputOutput() : void
    {
        $this->news->title = 'Title';
        self::assertEquals('Title', $this->news->title);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The content can be correctly set and returned')]
    public function testContentInputOutput() : void
    {
        $this->news->content = 'Content';
        self::assertEquals('Content', $this->news->content);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The plain content can be correctly set and returned')]
    public function testPlainInputOutput() : void
    {
        $this->news->plain = 'Plain';
        self::assertEquals('Plain', $this->news->plain);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The publish date can be correctly set and returned')]
    public function testPublishInputOutput() : void
    {
        $this->news->publish = $data = new \DateTime('2001-05-07');
        self::assertEquals($data, $this->news->publish);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The featured flag can be correctly set and returned')]
    public function testFeaturedInputOutput() : void
    {
        $this->news->isFeatured = true;
        self::assertTrue($this->news->isFeatured);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model can be correctly serialized')]
    public function testSerialization() : void
    {
        $this->news->title      = 'Title';
        $this->news->createdBy  = new NullAccount(1);
        $this->news->content    = 'Content';
        $this->news->plain      = 'Plain';
        $this->news->publish    = new \DateTime('2001-05-07');
        $this->news->isFeatured = true;
        $this->news->language   = ISO639x1Enum::_DE;
        $this->news->status     = NewsStatus::VISIBLE;
        $this->news->type       = NewsType::HEADLINE;

        $arr = [
            'id'         => 0,
            'title'      => $this->news->title,
            'plain'      => $this->news->plain,
            'content'    => $this->news->content,
            'type'       => $this->news->type,
            'status'     => $this->news->status,
            'isFeatured' => $this->news->isFeatured,
            'publish'    => $this->news->publish,
            'createdAt'  => $this->news->createdAt,
            'createdBy'  => $this->news->createdBy,
        ];

        self::assertEquals($arr, $this->news->toArray());
        self::assertEquals($arr, $this->news->jsonSerialize());
    }
}
