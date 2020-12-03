<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\News\Controller;

use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use Modules\News\Models\PermissionState;
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
 * @link    https://orange-management.org
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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                NewsArticleMapper::withConditional('language', $response->getLanguage())
                    ::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                    ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                    ::getBeforePivot((int) ($request->getData('id') ?? 0), null, 10)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                NewsArticleMapper::withConditional('language', $response->getLanguage())
                    ::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                    ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                    ::getAfterPivot((int) ($request->getData('id') ?? 0), null, 10)
            );
        } else {
            $view->setData('news',
                NewsArticleMapper::withConditional('language', $response->getLanguage())
                    ::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                    ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                    ::getAfterPivot(0, null, 10)
            );
        }

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/News/Theme/Backend/dashboard-news');

        $news = NewsArticleMapper::withConditional('language', $response->getLanguage())
            ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
            ::getNewest(5);

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

        $article   = NewsArticleMapper::get((int) $request->getData('id'));
        $accountId = $request->header->account;

        if ($article->createdBy->getId() !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::MODULE_NAME, PermissionState::NEWS, $article->getId())
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/News/Theme/Backend/news-single');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));
        $view->addData('news', $article);
        $view->addData('editable', $this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->orgId, $this->app->appName, self::MODULE_NAME, PermissionState::NEWS, $article->getId())
        );

        // allow comments
        if (!$article->getComments() !== null
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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                NewsArticleMapper::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                    ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                    ::getBeforePivot((int) ($request->getData('id') ?? 0), null, 25)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                NewsArticleMapper::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                    ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                    ::getAfterPivot((int) ($request->getData('id') ?? 0), null, 25)
            );
        } else {
            $view->setData('news', NewsArticleMapper::withConditional('status', NewsStatus::VISIBLE, [NewsArticle::class])
                ::withConditional('publish', new \DateTime('now'), [NewsArticle::class], '<=')
                ::getAfterPivot(0, null, 25));
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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('news',
                NewsArticleMapper::getBeforePivot((int) ($request->getData('id') ?? 0), null, 25)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('news',
                NewsArticleMapper::getAfterPivot((int) ($request->getData('id') ?? 0), null, 25)
            );
        } else {
            $view->setData('news', NewsArticleMapper::getAfterPivot(0, null, 25));
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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));

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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000701001, $request, $response));

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        $view->addData('news', NewsArticleMapper::get((int) ($request->getData('id') ?? 0)));

        return $view;
    }
}
