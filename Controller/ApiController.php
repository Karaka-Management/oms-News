<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
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
 * @license OMS License 2.0
 * @link    https://jingga.app
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
        if (($val['title'] = !$request->hasData('title'))
            || ($val['plain'] = !$request->hasData('plain'))
            || ($val['lang'] = (
                $request->hasData('lang')
                && !ISO639x1Enum::isValidValue(\strtolower((string) $request->getData('lang')))
            ))
            || ($val['type'] = (
                !$request->hasData('type')
                || !NewsType::isValidValue((int) $request->getData('type'))
            ))
            || ($val['status'] = (
                !$request->hasData('status')
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
        /** @var \Modules\News\Models\NewsArticle $old */
        $old = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $old = clone $old;
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
        /** @var \Modules\News\Models\NewsArticle $newsArticle */
        $newsArticle          = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $newsArticle->publish = new \DateTime((string) ($request->getData('publish') ?? $newsArticle->publish->format('Y-m-d H:i:s')));
        $newsArticle->title   = $request->getDataString('title') ?? $newsArticle->title;
        $newsArticle->plain   = $request->getDataString('plain') ?? $newsArticle->plain;
        $newsArticle->content = Markdown::parse($request->getDataString('plain') ?? $newsArticle->plain);
        $newsArticle->setLanguage(\strtolower($request->getDataString('lang') ?? $newsArticle->getLanguage()));
        $newsArticle->setType($request->getDataInt('type') ?? $newsArticle->getType());
        $newsArticle->setStatus($request->getDataInt('status') ?? $newsArticle->getStatus());
        $newsArticle->isFeatured = $request->getDataBool('featured') ?? $newsArticle->isFeatured;
        $newsArticle->unit       = $request->getDataInt('unit');
        $newsArticle->app        = $request->getDataInt('app');

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
        $path = $this->createNewsDir($news);

        /** @var \Modules\Admin\Models\Account $account */
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if (!empty($uploadedFiles = $request->getFiles())) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH
            );

            $collection = null;

            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $news->getId(),
                    $media->getId(),
                    NewsArticleMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );

                $accountPath = '/Accounts/' . $account->getId() . ' ' . $account->login
                    . '/News/'
                    . $news->createdAt->format('Y') . '/' . $news->createdAt->format('m')
                    . '/' . $news->getId();

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

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

                $this->createModelRelation(
                    $request->header->account,
                    $collection->getId(),
                    $ref->getId(),
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $news->getId(),
                    (int) $media,
                    NewsArticleMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );

                /** @var \Modules\Media\Models\Media $mediaObject */
                $mediaObject = MediaMapper::get()
                    ->where('id', (int) $media)
                    ->execute();

                $ref            = new Reference();
                $ref->source    = new NullMedia((int) $media);
                $ref->name      = $mediaObject->name;
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

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

                $this->createModelRelation(
                    $request->header->account,
                    $collection->getId(),
                    $ref->getId(),
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
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
        $newsArticle->title     = $request->getDataString('title') ?? '';
        $newsArticle->plain     = $request->getDataString('plain') ?? '';
        $newsArticle->content   = Markdown::parse($request->getDataString('plain') ?? '');
        $newsArticle->setLanguage(\strtolower($request->getDataString('lang') ?? $request->getLanguage()));
        $newsArticle->setType($request->getDataInt('type') ?? NewsType::ARTICLE);
        $newsArticle->setStatus($request->getDataInt('status') ?? NewsStatus::VISIBLE);
        $newsArticle->isFeatured = $request->getDataBool('featured') ?? true;

        // allow comments
        if ($request->hasData('allow_comments')
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

                    if (!\is_array($data = $internalResponse->get($request->uri->__toString()))) {
                        continue;
                    }

                    $newsArticle->addTag($data['response']);
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
