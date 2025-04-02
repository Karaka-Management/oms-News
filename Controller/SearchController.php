<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Controller;

use Modules\News\Models\NewsArticleMapper;
use Modules\News\Models\NewsStatus;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;

/**
 * Search class.
 *
 * @package Modules\News
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class SearchController extends Controller
{
    /**
     * Api method to search for tags
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
    public function searchTag(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $search = $request->getDataString('search') ?? '';

        $searchIdStartPos = \stripos($search, ':');
        $patternStartPos  = $searchIdStartPos === false
            ? -1
            : \stripos($search, ' ', $searchIdStartPos);

        $pattern = \substr($search, $patternStartPos + 1);

        /** @var \Modules\News\Models\NewsArticle[] $news */
        $news = NewsArticleMapper::getAll()
            ->with('tags')
            ->with('tags/title')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->header->l11n->language)
            ->where('tags/title/language', $response->header->l11n->language)
            ->where('tags/title/content', $pattern)
            ->sort('publish', OrderType::DESC)
            ->limit(8)
            ->executeGetArray();

        $results = [];
        foreach ($news as $article) {
            $results[] = [
                'title'     => $article->title,
                'summary'   => '',
                'link'      => '{/base}/news/article?id=' . $article->id,
                'account'   => '',
                'createdAt' => $article->createdAt,
                'image'     => '',
                'tags'      => $article->tags,
                'type'      => 'list_links',
                'module'    => 'News',
            ];
        }

        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);
        $response->add($request->uri->__toString(), $results);
    }

    /**
     * Api method to search for tags
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
    public function searchGeneral(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\News\Models\NewsArticle[] $news */
        $news = NewsArticleMapper::getAll()
            ->with('tags')
            ->with('tags/title')
            ->where('title', '%' . ($request->getDataString('search') ?? '') . '%', 'LIKE')
            ->where('plain', '%' . ($request->getDataString('search') ?? '') . '%', 'LIKE', 'OR')
            ->where('status', NewsStatus::VISIBLE)
            ->where('publish', new \DateTime('now'), '<=')
            ->where('language', $response->header->l11n->language)
            ->where('tags/title/language', $response->header->l11n->language)
            ->sort('publish', OrderType::DESC)
            ->limit(8)
            ->executeGetArray();

        $results = [];
        foreach ($news as $article) {
            $results[] = [
                'title'     => $article->title,
                'summary'   => '',
                'link'      => '{/base}/news/article?id=' . $article->id,
                'account'   => '',
                'createdAt' => $article->createdAt,
                'image'     => '',
                'tags'      => $article->tags,
                'type'      => 'list_links',
                'module'    => 'News',
            ];
        }

        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);
        $response->add($request->uri->__toString(), $results);
    }
}
