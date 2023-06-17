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
use Modules\Media\Models\Media;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use Modules\Tag\Models\Tag;
use phpOMS\Localization\ISO639x1Enum;

/**
 * @testdox Modules\News\tests\Models\NewsArticleTest: News article
 *
 * @internal
 */
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

    /**
     * @testdox The model has the expected default values after initialization
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->news->id);
        self::assertEquals(0, $this->news->createdBy->id);
        self::assertEquals('', $this->news->title);
        self::assertEquals('', $this->news->content);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->news->createdAt->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->news->publish->format('Y-m-d'));
        self::assertFalse($this->news->isFeatured);
        self::assertEquals(ISO639x1Enum::_EN, $this->news->getLanguage());
        self::assertEquals(NewsStatus::DRAFT, $this->news->getStatus());
        self::assertEquals(NewsType::ARTICLE, $this->news->getType());
        self::assertEquals([], $this->news->getTags());
        self::assertEquals('', $this->news->plain);
    }

    /**
     * @testdox The creator can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testCreatorInputOutput() : void
    {
        $this->news->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->news->createdBy->id);
    }

    /**
     * @testdox The title can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testTitleInputOutput() : void
    {
        $this->news->title = 'Title';
        self::assertEquals('Title', $this->news->title);
    }

    /**
     * @testdox The content can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testContentInputOutput() : void
    {
        $this->news->content = 'Content';
        self::assertEquals('Content', $this->news->content);
    }

    /**
     * @testdox The plain content can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testPlainInputOutput() : void
    {
        $this->news->plain = 'Plain';
        self::assertEquals('Plain', $this->news->plain);
    }

    /**
     * @testdox The publish date can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testPublishInputOutput() : void
    {
        $this->news->publish = $data = new \DateTime('2001-05-07');
        self::assertEquals($data, $this->news->publish);
    }

    /**
     * @testdox The featured flag can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testFeaturedInputOutput() : void
    {
        $this->news->isFeatured = true;
        self::assertTrue($this->news->isFeatured);
    }

    /**
     * @testdox The language can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testLanguageInputOutput() : void
    {
        $this->news->setLanguage(ISO639x1Enum::_DE);
        self::assertEquals(ISO639x1Enum::_DE, $this->news->getLanguage());
    }

    /**
     * @testdox The langague can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testStatusInputOutput() : void
    {
        $this->news->setStatus(NewsStatus::VISIBLE);
        self::assertEquals(NewsStatus::VISIBLE, $this->news->getStatus());
    }

    /**
     * @testdox The type can be correctly set and returned
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testTypeInputOutput() : void
    {
        $this->news->setType(NewsType::HEADLINE);
        self::assertEquals(NewsType::HEADLINE, $this->news->getType());
    }

    /**
     * @testdox The model can be correctly serialized
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testSerialization() : void
    {
        $this->news->title      = 'Title';
        $this->news->createdBy  = new NullAccount(1);
        $this->news->content    = 'Content';
        $this->news->plain      = 'Plain';
        $this->news->publish    = new \DateTime('2001-05-07');
        $this->news->isFeatured = true;
        $this->news->setLanguage(ISO639x1Enum::_DE);
        $this->news->setStatus(NewsStatus::VISIBLE);
        $this->news->setType(NewsType::HEADLINE);

        $arr = [
            'id'          => 0,
            'title'       => $this->news->title,
            'plain'       => $this->news->plain,
            'content'     => $this->news->content,
            'type'        => $this->news->getType(),
            'status'      => $this->news->getStatus(),
            'isFeatured'  => $this->news->isFeatured,
            'publish'     => $this->news->publish,
            'createdAt'   => $this->news->createdAt,
            'createdBy'   => $this->news->createdBy,
        ];

        self::assertEquals($arr, $this->news->toArray());
        self::assertEquals($arr, $this->news->jsonSerialize());
    }

    /**
     * @testdox A invalid status throws a InvalidEnumValue exception
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testInvalidStatus() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $news = new NewsArticle();
        $news->setStatus(9999);
    }

    /**
     * @testdox A invalid type throws a InvalidEnumValue exception
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testInvalidType() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $news = new NewsArticle();
        $news->setType(9999);
    }

    /**
     * @testdox A invalid language throws a InvalidEnumValue exception
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testInvalidLanguage() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $news = new NewsArticle();
        $news->setLanguage('9999');
    }

    /**
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testTagInputOutput() : void
    {
        $tag = new Tag();
        $tag->setL11n('Tag');

        $this->news->addTag($tag);
        self::assertEquals($tag, $this->news->getTag(0));
        self::assertCount(1, $this->news->getTags());
    }

    /**
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testTagRemove() : void
    {
        $tag = new Tag();
        $tag->setL11n('Tag');

        $this->news->addTag($tag);
        self::assertTrue($this->news->removeTag(0));
        self::assertCount(0, $this->news->getTags());
        self::assertFalse($this->news->removeTag(0));
    }

    /**
     * @covers Modules\News\Models\NewsArticle
     * @group module
     */
    public function testMediaInputOutput() : void
    {
        $this->news->addMedia(new Media());
        self::assertCount(1, $this->news->getMedia());
    }
}
