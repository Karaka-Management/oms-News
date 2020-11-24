<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use \phpOMS\Uri\UriFactory;
use phpOMS\Utils\Parser\Markdown\Markdown;

/** @var \phpOMS\Views\View $this */
/** @var \Modules\News\Models\NewsArticle[] $newsList */
$newsList = $this->getData('news');

$previous = empty($newsList) ? '{/prefix}news/dashboard' : '{/prefix}news/dashboard?{?}&id=' . \reset($newsList)->getId() . '&ptype=p';
$next     = empty($newsList) ? '{/prefix}news/dashboard' : '{/prefix}news/dashboard?{?}&id=' . \end($newsList)->getId() . '&ptype=n';

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <?php foreach ($newsList as $news) : $url = UriFactory::build('{/prefix}news/article?id=' . $news->getId()); ?>
        <div class="portlet">
            <div class="portlet-head"><a href="<?= $url; ?>"><?= $this->printHtml($news->title); ?></a></div>
            <div class="portlet-body">
                <article>
                    <?= Markdown::parse(\substr($news->plain, 0, 500)); ?>
                </article>
            </div>
            <div class="portlet-foot">
                <div class="overflowfix">
                    <?php $tags = $news->getTags(); foreach ($tags as $tag) : ?>
                        <span class="tag" style="background: <?= $this->printHtml($tag->getColor()); ?>"><?= $this->printHtml($tag->getTitle()); ?></span>
                    <?php endforeach; ?>
                    <a tabindex="0" href="<?= $url; ?>" class="button floatRight">More</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="plain-portlet">
        <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
        <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
    </div>
</div>
