<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
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
 * @testdox Modules\News\tests\Models\NewsArticleMapperTest: News article mapper
 *
 * @internal
 */
final class NewsArticleMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox The model can be created and read from the database
     * @covers Modules\News\Models\NewsArticleMapper
     * @group module
     */
    public function testCRUD() : void
    {
        $text = new Text();
        $news = new NewsArticle();

        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = ($raw = $text->generateText(\mt_rand(100, 300)));
        $news->plain     = $raw;
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(true);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::HEADLINE);

        $id = NewsArticleMapper::create($news);
        self::assertGreaterThan(0, $news->getId());
        self::assertEquals($id, $news->getId());

        $newsR = NewsArticleMapper::get($news->getId());
        self::assertEquals($news->createdAt->format('Y-m-d'), $newsR->createdAt->format('Y-m-d'));
        self::assertEquals($news->createdBy->getId(), $newsR->createdBy->getId());
        self::assertEquals($news->content, $newsR->content);
        self::assertEquals($news->plain, $newsR->plain);
        self::assertEquals($news->title, $newsR->title);
        self::assertEquals($news->getStatus(), $newsR->getStatus());
        self::assertEquals($news->getType(), $newsR->getType());
        self::assertEquals($news->getLanguage(), $newsR->getLanguage());
        self::assertEquals($news->isFeatured(), $newsR->isFeatured());
        self::assertEquals($news->getPublish()->format('Y-m-d'), $newsR->getPublish()->format('Y-m-d'));
    }

    /**
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testVolume() : void
    {
        $text = new Text();

        // Created by other

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::HEADLINE);

        $id = NewsArticleMapper::create($news);

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::DRAFT);
        $news->setType(NewsType::HEADLINE);

        $id = NewsArticleMapper::create($news);

        // Created by me

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::ARTICLE);

        $id = NewsArticleMapper::create($news);

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::LINK);

        $id = NewsArticleMapper::create($news);

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::DRAFT);
        $news->setType(NewsType::ARTICLE);

        $id = NewsArticleMapper::create($news);

        // Language

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish(new \DateTime('2001-05-07'));
        $news->setFeatured(true);
        $news->setLanguage(ISO639x1Enum::_EN);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::ARTICLE);

        $id = NewsArticleMapper::create($news);

        // Publish

        $publishDate = new \DateTime('now');
        $publishDate->modify('+1 days');

        $news            = new NewsArticle();
        $news->createdBy = new NullAccount(1);
        $news->title     = $text->generateText(\mt_rand(3, 7));
        $news->content   = $text->generateText(\mt_rand(10, 300));
        $news->setPublish($publishDate);
        $news->setFeatured(false);
        $news->setLanguage(ISO639x1Enum::_DE);
        $news->setStatus(NewsStatus::VISIBLE);
        $news->setType(NewsType::ARTICLE);

        $id = NewsArticleMapper::create($news);
    }
}
