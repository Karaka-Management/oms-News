<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\News\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\Reference;
use Modules\Media\Models\ReferenceMapper;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use Modules\Tag\Models\NullTag;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Module\NullModule;
use phpOMS\Utils\Parser\Markdown\Markdown;

/**
 * News controller class.
 *
 * @package Modules\News
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Validate news create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateNewsCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
            || ($val['lang'] = (
                $request->getData('lang') !== null
                && !ISO639x1Enum::isValidValue(\strtolower((string) $request->getData('lang')))
            ))
            || ($val['type'] = (
                $request->getData('type') === null
                || !NewsType::isValidValue((int) $request->getData('type'))
            ))
            || ($val['status'] = (
                $request->getData('status') === null
                || !NewsStatus::isValidValue((int) $request->getData('status'))
            ))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $old = clone NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateNewsFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, NewsArticleMapper::class, 'news', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'News', 'News successfully updated', $new);
    }

    /**
     * Method to update news from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return NewsArticle
     *
     * @since 1.0.0
     */
    private function updateNewsFromRequest(RequestAbstract $request) : NewsArticle
    {
        /** @var NewsArticle $newsArticle */
        $newsArticle          = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $newsArticle->publish = new \DateTime((string) ($request->getData('publish') ?? $newsArticle->publish->format('Y-m-d H:i:s')));
        $newsArticle->title   = (string) ($request->getData('title') ?? $newsArticle->title);
        $newsArticle->plain   = $request->getData('plain') ?? $newsArticle->plain;
        $newsArticle->content = Markdown::parse((string) ($request->getData('plain') ?? $newsArticle->plain));
        $newsArticle->setLanguage(\strtolower((string) ($request->getData('lang') ?? $newsArticle->getLanguage())));
        $newsArticle->setType((int) ($request->getData('type') ?? $newsArticle->getType()));
        $newsArticle->setStatus((int) ($request->getData('status') ?? $newsArticle->getStatus()));
        $newsArticle->isFeatured = (bool) ($request->getData('featured') ?? $newsArticle->isFeatured);

        return $newsArticle;
    }

    /**
     * Api method to create news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateNewsCreate($request))) {
            $response->set('news_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $newsArticle = $this->createNewsArticleFromRequest($request);
        $this->createModel($request->header->account, $newsArticle, NewsArticleMapper::class, 'news', $request->getOrigin());

        if (!empty($request->getFiles())
            || !empty($request->getDataJson('media'))
        ) {
            $this->createNewsMedia($newsArticle, $request);
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'News', 'News successfully created', $newsArticle);
    }

    /**
     * Create media files for news article
     *
     * @param NewsArticle     $news    News article
     * @param RequestAbstract $request Request incl. media do upload
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createNewsMedia(NewsArticle $news, RequestAbstract $request) : void
    {
        $path    = $this->createNewsDir($news);
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if (!empty($uploadedFiles = $request->getFiles())) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [],
                [],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path,
                $path,
            );

            $collection = null;

            foreach ($uploaded as $media) {
                MediaMapper::create()->execute($media);
                NewsArticleMapper::writer()->createRelationTable('media', [$media->getId()], $news->getId());

                $accountPath = '/Accounts/' . $account->getId() . ' ' . $account->login . '/News/' . $news->createdAt->format('Y') . '/' . $news->createdAt->format('m') . '/' . $news->getId();

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection instanceof NullCollection) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $accountPath,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->getId() . '/News/' . $news->createdAt->format('Y') . '/' . $news->createdAt->format('m') . '/' . $news->getId()
                        );
                    }
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $media) {
                NewsArticleMapper::writer()->createRelationTable('media', [(int) $media], $news->getId());

                $ref            = new Reference();
                $ref->source    = new NullMedia((int) $media);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection instanceof NullCollection) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $path,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files' . $path
                        );
                    }
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }
    }

    /**
     * Create media directory path
     *
     * @param NewsArticle $news News article
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createNewsDir(NewsArticle $news) : string
    {
        return '/Modules/News/'
            . $news->createdAt->format('Y') . '/'
            . $news->createdAt->format('m') . '/'
            . $news->createdAt->format('d') . '/'
            . $news->getId();
    }

    /**
     * Method to create news article from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return NewsArticle
     *
     * @since 1.0.0
     */
    private function createNewsArticleFromRequest(RequestAbstract $request) : NewsArticle
    {
        $newsArticle            = new NewsArticle();
        $newsArticle->createdBy = new NullAccount($request->header->account);
        $newsArticle->publish   = new \DateTime((string) ($request->getData('publish') ?? 'now'));
        $newsArticle->title     = (string) ($request->getData('title') ?? '');
        $newsArticle->plain     = $request->getData('plain') ?? '';
        $newsArticle->content   = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $newsArticle->setLanguage(\strtolower((string) ($request->getData('lang') ?? $request->getLanguage())));
        $newsArticle->setType((int) ($request->getData('type') ?? NewsType::ARTICLE));
        $newsArticle->setStatus((int) ($request->getData('status') ?? NewsStatus::VISIBLE));
        $newsArticle->isFeatured = (bool) ($request->getData('featured') ?? true);

        // allow comments
        if (!empty($request->getData('allow_comments'))
            && !(($commentApi = $this->app->moduleManager->get('Comments')) instanceof NullModule)
        ) {
            /** @var \Modules\Comments\Controller\ApiController $commentApi */
            $commnetList           = $commentApi->createCommentList();
            $newsArticle->comments = $commnetList;
        }

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, null);
                    $newsArticle->addTag($internalResponse->get($request->uri->__toString())['response']);
                } else {
                    $newsArticle->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        return $newsArticle;
    }

    /**
     * Api method to get a news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsGet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $news = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'News', 'News successfully returned', $news);
    }

    /**
     * Api method to delete news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $news = NewsArticleMapper::get()->with('media')->with('tags')->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $news, NewsArticleMapper::class, 'news', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'News', 'News successfully deleted', $news);
    }
}
