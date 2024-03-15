<?php
/**
 * Jingga
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
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\Reference;
use Modules\Media\Models\ReferenceMapper;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use Modules\News\Models\PermissionCategory;
use Modules\Notification\Models\Notification;
use Modules\Notification\Models\NotificationMapper;
use Modules\Notification\Models\NotificationType;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
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
     * Create notification for new articles
     *
     * @param NewsArticle     $article News article
     * @param RequestAbstract $request Request
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createNotifications(NewsArticle $article, RequestAbstract $request) : void
    {
        $accounts = AccountMapper::findReadPermission(
            $this->app->unitId,
            self::NAME,
            PermissionCategory::NEWS,
            $article->id
        );

        foreach ($accounts as $account) {
            $notification             = new Notification();
            $notification->module     = self::NAME;
            $notification->title      = $article->title;
            $notification->createdAt  = \DateTimeImmutable::createFromMutable($article->publish);
            $notification->createdBy  = $article->createdBy;
            $notification->createdFor = new NullAccount($account);
            $notification->type       = NotificationType::CREATE;
            $notification->category   = PermissionCategory::NEWS;
            $notification->element    = $article->id;
            $notification->redirect   = '{/base}/news/article?{?}&id=' . $article->id;

            $this->createModel($request->header->account, $notification, NotificationMapper::class, 'notification', $request->getOrigin());
        }
    }

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
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\News\Models\NewsArticle $old */
        $old = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateNewsFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, NewsArticleMapper::class, 'news', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
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
    private function updateNewsFromRequest(RequestAbstract $request, NewsArticle $new) : NewsArticle
    {
        $new->publish    = $request->hasData('publish') ? new \DateTime($request->getDataString('publish') ?? 'now') : $new->publish;
        $new->title      = $request->getDataString('title') ?? $new->title;
        $new->plain      = $request->getDataString('plain') ?? $new->plain;
        $new->content    = Markdown::parse($request->getDataString('plain') ?? $new->plain);
        $new->language   = ISO639x1Enum::tryFromValue($request->getDataString('lang')) ?? $new->language;
        $new->type       = NewsType::tryFromValue($request->getDataInt('type')) ?? $new->type;
        $new->status     = NewsStatus::tryFromValue($request->getDataInt('status')) ?? $new->status;
        $new->isFeatured = $request->getDataBool('featured') ?? $new->isFeatured;
        $new->unit       = $request->getDataInt('unit');
        $new->app        = $request->getDataInt('app');

        return $new;
    }

    /**
     * Api method to create news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateNewsCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $newsArticle = $this->createNewsArticleFromRequest($request);
        $this->createModel($request->header->account, $newsArticle, NewsArticleMapper::class, 'news', $request->getOrigin());

        $this->createNotifications($newsArticle, $request);

        if (!empty($request->files)
            || !empty($request->getDataJson('media'))
        ) {
            $this->createNewsMedia($newsArticle, $request);
        }

        $this->createStandardCreateResponse($request, $response, $newsArticle);
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

        $collection = null;

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH
            );

            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $news->id,
                    $media->id,
                    NewsArticleMapper::class,
                    'files',
                    '',
                    $request->getOrigin()
                );

                $accountPath = '/Accounts/' . $account->id . ' ' . $account->login
                    . '/News/'
                    . $news->createdAt->format('Y') . '/' . $news->createdAt->format('m')
                    . '/' . $news->id;

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->id);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

                if ($collection === null) {
                    /** @var \Modules\Media\Models\Collection $collection */
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection->id === 0) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $accountPath,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->id . '/News/' . $news->createdAt->format('Y') . '/' . $news->createdAt->format('m') . '/' . $news->id
                        );
                    }
                }

                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $ref->id,
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        $mediaFiles = $request->getDataJson('media');
        foreach ($mediaFiles as $media) {
            $this->createModelRelation(
                $request->header->account,
                $news->id,
                (int) $media,
                NewsArticleMapper::class,
                'files',
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
                /** @var \Modules\Media\Models\Collection $collection */
                $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                if ($collection->id === 0) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
                }
            }

            $this->createModelRelation(
                $request->header->account,
                $collection->id,
                $ref->id,
                CollectionMapper::class,
                'sources',
                '',
                $request->getOrigin()
            );
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
            . $news->id;
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
        $newsArticle             = new NewsArticle();
        $newsArticle->createdBy  = new NullAccount($request->header->account);
        $newsArticle->publish    = new \DateTime($request->getDataString('publish') ?? 'now');
        $newsArticle->title      = $request->getDataString('title') ?? '';
        $newsArticle->plain      = $request->getDataString('plain') ?? '';
        $newsArticle->content    = Markdown::parse($request->getDataString('plain') ?? '');
        $newsArticle->language   = ISO639x1Enum::tryFromValue($request->getDataString('lang')) ?? $request->header->l11n->language;
        $newsArticle->type       = NewsType::tryFromValue($request->getDataInt('type')) ?? NewsType::ARTICLE;
        $newsArticle->status     = NewsStatus::tryFromValue($request->getDataInt('status')) ?? NewsStatus::VISIBLE;
        $newsArticle->isFeatured = $request->getDataBool('featured') ?? true;
        $newsArticle->unit       = $request->getDataInt('unit') ?? null;

        // allow comments
        if ($request->hasData('allow_comments')
            && ($commentApi = $this->app->moduleManager->get('Comments', 'Api'))::ID > 0
        ) {
            /** @var \Modules\Comments\Controller\ApiController $commentApi */
            $commnetList           = $commentApi->createCommentList();
            $newsArticle->comments = $commnetList;
        }

        if ($request->hasData('tags')) {
            $newsArticle->tags = $this->app->moduleManager->get('Tag', 'Api')->createTagsFromRequest($request);
        }

        return $newsArticle;
    }

    /**
     * Api method to get a news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $news = NewsArticleMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->createStandardReturnResponse($request, $response, $news);
    }

    /**
     * Api method to delete news article
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNewsDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $news = NewsArticleMapper::get()->with('files')->with('tags')->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $news, NewsArticleMapper::class, 'news', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $news);
    }
}
