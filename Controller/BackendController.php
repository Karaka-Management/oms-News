<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\News\Controller;

use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsSeen;
use Modules\News\Models\NewsSeenMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\NullNewsSeen;
use Modules\News\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Module\NullModule;
use phpOMS\Views\View;

/**
 * News controller class.
 *
 * @package Modules\News
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class BackendController extends Controller implements DashboardElementInterface
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-dashboard');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        $mapperQuery = NewsArticleMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->getLanguage())
            ->where('tags/title/language', $response->getLanguage());

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                $data = $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->limit(25)->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                $data = $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->limit(25)->execute()
            );
        } else {
            $view->setData('news',
                $data = $mapperQuery->where('id', 0, '>')
                    ->limit(25)->execute()
            );
        }

        $ids = [];
        foreach ($data as $news) {
            $ids[] = $news->getId();
        }

        $seenObjects = NewsSeenMapper::getAll()
            ->where('seenBy', $request->header->account)
            ->where('news', $ids, 'in')
            ->execute();

        $seen = [];
        foreach ($seenObjects as $seenObject) {
            $seen[] = $seenObject->news;
        }

        $view->setData('seen', $seen);

        return $view;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/News/Theme/Backend/dashboard-news');

        $news = NewsArticleMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->getLanguage())
            ->where('tags/title/language', $response->getLanguage())
            ->where('id', 0, '>')
            ->limit(5)
            ->execute();

        $view->addData('news', $news);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsArticle(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $article = NewsArticleMapper::get()
            ->with('createdBy')
            ->with('comments')
            ->with('comments/comments')
            ->with('comments/comments/createdBy')
            ->with('comments/comments/media')
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->getLanguage())
            ->where('tags/title/language', $response->getLanguage())
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;

        if ($article->createdBy->getId() !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::NEWS, $article->getId())
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $seen = NewsSeenMapper::get()
            ->where('news', (int) $request->getData('id'))
            ->where('seenBy', $request->header->account)
            ->execute();

        if ($seen instanceof NullNewsSeen) {
            $seen         = new NewsSeen();
            $seen->seenBy = (int) $request->header->account;
            $seen->news   = (int) $request->getData('id');
            $seen->seenAt = new \DateTime('now');

            NewsSeenMapper::create()->execute($seen);
        }

        $view->setTemplate('/Modules/News/Theme/Backend/news-single');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));
        $view->addData('news', $article);
        $view->addData('editable', $this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::NEWS, $article->getId())
        );

        // allow comments
        if (!$article->comments !== null
            && !($this->app->moduleManager->get('Comments') instanceof NullModule)
        ) {
            $commentCreateView = new \Modules\Comments\Theme\Backend\Components\Comment\CreateView($this->app->l11nManager, $request, $response);
            $commentListView   = new \Modules\Comments\Theme\Backend\Components\Comment\ListView($this->app->l11nManager, $request, $response);

            $view->addData('commentCreate', $commentCreateView);
            $view->addData('commentList', $commentListView);
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsArchive(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-archive');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        $mapperQuery =  NewsArticleMapper::getAll()
        ->with('createdBy')
        ->with('tags')
        ->with('tags/title')
        ->where('status', NewsStatus::VISIBLE)
        ->where('publish', new \DateTime('now'), '<=')
        ->where('language', $response->getLanguage())
        ->where('tags/title/language', $response->getLanguage());

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->limit(25)->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->limit(25)->execute()
            );
        } else {
            $view->setData('news',
                $mapperQuery->where('id', 0, '>')
                    ->limit(25)->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsDraftList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-draft');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                NewsArticleMapper::getAll()->where('id', (int) ($request->getData('id') ?? 0), '<')->limit(25)->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                NewsArticleMapper::getAll()->where('id', (int) ($request->getData('id') ?? 0), '>')->limit(25)->execute()
            );
        } else {
            $view->setData('news', NewsArticleMapper::getAll()->where('id', 0, '>')->limit(25)->execute());
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsEdit(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        $view->addData('news', NewsArticleMapper::get()->where('id', (int) ($request->getData('id') ?? 0))->execute());

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewNewsAnalysis(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/News/Theme/Backend/news-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000601001, $request, $response));

        return $view;
    }
}
