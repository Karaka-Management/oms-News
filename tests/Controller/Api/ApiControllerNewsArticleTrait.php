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

namespace Modules\News\tests\Controller\Api;

use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use Modules\News\Models\NullNewsArticle;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\System\MimeType;
use phpOMS\Uri\HttpUri;
use phpOMS\Utils\TestUtils;

trait ApiControllerNewsArticleTrait
{
    /**
     * @testdox A news article can be created
     * @covers Modules\News\Controller\ApiController
     * @group module
     */
    public function testApiNewsCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('title', 'Controller Test Title');
        $request->setData('plain', 'Controller Test Content');
        $request->setData('lang', 'en');
        $request->setData('type', NewsType::ARTICLE);
        $request->setData('status', NewsStatus::DRAFT);
        $request->setData('featred', true);
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        if (!\is_file(__DIR__ . '/test_tmp.md')) {
            \copy(__DIR__ . '/test.md', __DIR__ . '/test_tmp.md');
        }

        TestUtils::setMember($request, 'files', [
            'file1' => [
                'name'     => 'test.md',
                'type'     => MimeType::M_TXT,
                'tmp_name' => __DIR__ . '/test_tmp.md',
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/test_tmp.md'),
            ],
        ]);

        $request->setData('media', \json_encode([1]));

        $this->module->apiNewsCreate($request, $response);

        self::assertEquals('Controller Test Title', $response->get('')['response']->title);
        self::assertGreaterThan(0, $response->get('')['response']->getId());
    }

    /**
     * @covers Modules\News\Controller\ApiController
     * @group module
     */
    public function testApiNewsCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiNewsCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @testdox A news article can be returned
     * @covers Modules\News\Controller\ApiController
     * @group module
     */
    public function testApiNewsGet() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', '1');

        $this->module->apiNewsGet($request, $response);

        self::assertGreaterThan(0, $response->get('')['response']->getId());
    }

    /**
     * @testdox A news article can be updated
     * @covers Modules\News\Controller\ApiController
     * @group module
     */
    public function testApiNewsUpdate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', 1);
        $request->setData('title', 'New Title');
        $request->setData('plain', 'New Content here');

        $this->module->apiNewsUpdate($request, $response);
        $this->module->apiNewsGet($request, $response);

        self::assertEquals('New Title', $response->get('')['response']->title);
    }

    /**
     * @testdox A news article can be deleted
     * @covers Modules\News\Controller\ApiController
     * @group module
     */
    public function testApiNewsDelete() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', 1);
        $this->module->apiNewsDelete($request, $response);

        self::assertEquals(1, $response->get('')['response']->getId());
        self::assertInstanceOf(NullNewsArticle::class, NewsArticleMapper::get()->where('id', 1)->execute());
    }
}
