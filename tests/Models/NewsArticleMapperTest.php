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

use Modules\Admin\Models\NullAccount;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Utils\RnG\Text;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\News\Models\NewsArticleMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\News\tests\Models\NewsArticleMapperTest: News article mapper')]
final class NewsArticleMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model can be created and read from the database')]
    public function testCRUD() : void
    {
        $text = new Text();
        $news = new NewsArticle();

        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = ($raw = $text->generateText(\mt_rand(100, 300)));
        $news->plain      = $raw;
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = true;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::HEADLINE;

        $id = NewsArticleMapper::create()->execute($news);
        self::assertGreaterThan(0, $news->id);
        self::assertEquals($id, $news->id);

        $newsR = NewsArticleMapper::get()->where('id', $news->id)->execute();
        self::assertEquals($news->createdAt->format('Y-m-d'), $newsR->createdAt->format('Y-m-d'));
        self::assertEquals($news->createdBy->id, $newsR->createdBy->id);
        self::assertEquals($news->content, $newsR->content);
        self::assertEquals($news->plain, $newsR->plain);
        self::assertEquals($news->title, $newsR->title);
        self::assertEquals($news->status, $newsR->status);
        self::assertEquals($news->type, $newsR->type);
        self::assertEquals($news->language, $newsR->language);
        self::assertEquals($news->isFeatured, $newsR->isFeatured);
        self::assertEquals($news->publish->format('Y-m-d'), $newsR->publish->format('Y-m-d'));
    }

    #[\PHPUnit\Framework\Attributes\Group('volume')]
    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testVolume() : void
    {
        $text = new Text();

        // Created by other

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::HEADLINE;

        $id = NewsArticleMapper::create()->execute($news);

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::DRAFT;
        $news->type       = NewsType::HEADLINE;

        $id = NewsArticleMapper::create()->execute($news);

        // Created by me

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::ARTICLE;

        $id = NewsArticleMapper::create()->execute($news);

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::LINK;

        $id = NewsArticleMapper::create()->execute($news);

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::DRAFT;
        $news->type       = NewsType::ARTICLE;

        $id = NewsArticleMapper::create()->execute($news);

        // Language

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = new \DateTime('2001-05-07');
        $news->isFeatured = true;
        $news->language   = ISO639x1Enum::_EN;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::ARTICLE;

        $id = NewsArticleMapper::create()->execute($news);

        // Publish

        $publishDate = new \DateTime('now');
        $publishDate->modify('+1 days');

        $news             = new NewsArticle();
        $news->createdBy  = new NullAccount(1);
        $news->title      = $text->generateText(\mt_rand(3, 7));
        $news->content    = $text->generateText(\mt_rand(10, 300));
        $news->publish    = $publishDate;
        $news->isFeatured = false;
        $news->language   = ISO639x1Enum::_DE;
        $news->status     = NewsStatus::VISIBLE;
        $news->type       = NewsType::ARTICLE;

        $id = NewsArticleMapper::create()->execute($news);
    }
}
