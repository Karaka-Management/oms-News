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

use Modules\Comments\Models\PermissionCategory as NewsPermissionCategory;
use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsSeen;
use Modules\News\Models\NewsSeenMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * News controller class.
 *
 * @package Modules\News
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class BackendController extends Controller implements DashboardElementInterface
{
    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        $mapperQuery = NewsArticleMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->header->l11n->language)
            ->where('tags/title/language', $response->header->l11n->language);

        /** @var \Modules\News\Models\NewsArticle[] $objs */
        $objs = [];
        if ($request->getData('ptype') === 'p') {
            /** @var \Modules\News\Models\NewsArticle[] $objs */
            $objs = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->limit(25)->execute();

            $view->data['news'] = $objs;
        } elseif ($request->getData('ptype') === 'n') {
            /** @var \Modules\News\Models\NewsArticle[] $objs */
            $objs = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->limit(25)->execute();

            $view->data['news'] = $objs;
        } else {
            /** @var \Modules\News\Models\NewsArticle[] $objs */
            $objs = $mapperQuery->where('id', 0, '>')
                    ->limit(25)->execute();

            $view->data['news'] = $objs;
        }

        $ids = [];
        foreach ($objs as $news) {
            $ids[] = $news->id;
        }

        /** @var \Modules\News\Models\NewsSeen[] $seenObjects */
        $seenObjects = NewsSeenMapper::getAll()
            ->where('seenBy', $request->header->account)
            ->where('news', $ids, 'in')
            ->execute();

        $seen = [];
        foreach ($seenObjects as $seenObject) {
            $seen[] = $seenObject->news;
        }

        $view->data['seen'] = $seen;

        return $view;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/News/Theme/Backend/dashboard-news');

        /** @var \Modules\News\Models\NewsArticle[] $news */
        $news = NewsArticleMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->header->l11n->language)
            ->where('tags/title/language', $response->header->l11n->language)
            ->where('id', 0, '>')
            ->limit(5)
            ->execute();

        $view->data['news'] = $news;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsArticle(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \Modules\News\Models\NewsArticle $article */
        $article = NewsArticleMapper::get()
            ->with('createdBy')
            ->with('comments')
            ->with('comments/comments')
            ->with('comments/comments/createdBy')
            ->with('comments/comments/media')
            ->with('files')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->header->l11n->language)
            ->where('tags/title/language', $response->header->l11n->language)
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;

        if ($article->createdBy->id !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::NEWS, $article->id)
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        /** @var \Modules\News\Models\NewsSeen $seen */
        $seen = NewsSeenMapper::get()
            ->where('news', (int) $request->getData('id'))
            ->where('seenBy', $request->header->account)
            ->execute();

        if ($seen->id === 0) {
            $seen         = new NewsSeen();
            $seen->seenBy = (int) $request->header->account;
            $seen->news   = (int) $request->getData('id');
            $seen->seenAt = new \DateTime('now');

            NewsSeenMapper::create()->execute($seen);
        }

        $view->setTemplate('/Modules/News/Theme/Backend/news-view');
        $view->data['nav']      = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);
        $view->data['news']     = $article;
        $view->data['editable'] = $this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::NEWS, $article->id);

        // Comments module available
        $commentModule = $this->app->moduleManager->get('Comments');
        if ($commentModule::ID > 0) {
            $head = $response->data['Content']->head;
            $head->addAsset(AssetType::CSS, 'Modules/Comments/Theme/Backend/css/styles.css');

            $commentCreateView = new \Modules\Comments\Theme\Backend\Components\Comment\CreateView($this->app->l11nManager, $request, $response);
            $commentListView   = new \Modules\Comments\Theme\Backend\Components\Comment\ListView($this->app->l11nManager, $request, $response);

            $view->data['commentCreate'] = $commentCreateView;
            $view->data['commentList']   = $commentListView;

            $view->data['commentPermissions'] = [
                'moderation' => $this->app->accountManager->get($request->header->account)->hasPermission(
                    PermissionType::MODIFY, $this->app->unitId, $this->app->appId, $commentModule::NAME, NewsPermissionCategory::MODERATION, $article->comments->id ?? null
                ),
                'list_modify' => $this->app->accountManager->get($request->header->account)->hasPermission(
                    PermissionType::MODIFY, $this->app->unitId, $this->app->appId, $commentModule::NAME, NewsPermissionCategory::LIST, $article->comments->id ?? null
                ),
                'list_read' => $this->app->accountManager->get($request->header->account)->hasPermission(
                    PermissionType::READ, $this->app->unitId, $this->app->appId, $commentModule::NAME, NewsPermissionCategory::LIST, $article->comments->id ?? null
                ),
                'write' => $this->app->accountManager->get($request->header->account)->hasPermission(
                    PermissionType::READ, $this->app->unitId, $this->app->appId, $commentModule::NAME, NewsPermissionCategory::COMMENT, null
                ),
            ];
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsArchive(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-archive');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        $mapperQuery = NewsArticleMapper::getAll()
        ->with('createdBy')
        ->with('tags')
        ->with('tags/title')
        ->where('status', NewsStatus::VISIBLE)
        ->where('publish', new \DateTime('now'), '<=')
        ->where('language', $response->header->l11n->language)
        ->where('tags/title/language', $response->header->l11n->language);

        if ($request->getData('ptype') === 'p') {
            $view->data['news'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->limit(25)->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['news'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->limit(25)->execute();
        } else {
            $view->data['news'] = $mapperQuery->where('id', 0, '>')
                    ->limit(25)->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsDraftList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-draft');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        if ($request->getData('ptype') === 'p') {
            $view->data['news'] = NewsArticleMapper::getAll()->where('id', $request->getDataInt('id') ?? 0, '<')->where('status', NewsStatus::DRAFT)->limit(25)->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['news'] = NewsArticleMapper::getAll()->where('id', $request->getDataInt('id') ?? 0, '>')->where('status', NewsStatus::DRAFT)->limit(25)->execute();
        } else {
            $view->data['news'] = NewsArticleMapper::getAll()->where('id', 0, '>')->where('status', NewsStatus::DRAFT)->limit(25)->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $tagSelector               = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['tagSelector'] = $tagSelector;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsEdit(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $tagSelector               = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['tagSelector'] = $tagSelector;

        $view->data['news'] = NewsArticleMapper::get()->where('id', $request->getDataInt('id') ?? 0)->execute();

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsAnalysis(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-analysis');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response);

        return $view;
    }
}
