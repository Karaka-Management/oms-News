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

use Modules\News\Models\NewsType;
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
/** @var \Modules\News\Models\NewsArticle[] $newsList */
$articles = $this->getData('news') ?? [];

$previous = empty($newsList) ? '{/prefix}news/archive' : '{/prefix}news/archive?{?}&id=' . \reset($newsList)->getId() . '&ptype=p';
$next     = empty($newsList) ? '{/prefix}news/archive' : '{/prefix}news/archive?{?}&id=' . \end($newsList)->getId() . '&ptype=n';

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Archive'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table id="newsArchiveList" class="default">
                <thead>
                <tr>
                    <td><?= $this->getHtml('Type'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                    <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                    <td><?= $this->getHtml('Author'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                    <td><?= $this->getHtml('Date'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
            <tbody>
                <?php $count                                                  = 0; foreach ($articles as $key => $news) : ++$count; $url                                                  = UriFactory::build('{/prefix}news/article?{?}&id=' . $news->getId());
                    $color                                                    = 'darkred';
                    if ($news->getType() === NewsType::ARTICLE) { $color      = 'green'; }
                    elseif ($news->getType() === NewsType::HEADLINE) { $color = 'purple'; }
                    elseif ($news->getType() === NewsType::LINK) { $color     = 'yellow'; }
                ?>
                    <tr tabindex="0" data-href="<?= $url; ?>">
                        <td><span class="tag <?= $this->printHtml($color); ?>"><?= $this->getHtml('TYPE' . $news->getType()); ?></span></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($news->title); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($news->createdBy->name2 . ', ' . $news->createdBy->name1); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($news->getPublish()->format('Y-m-d')); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="4" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            <div class="portlet-foot">
                <a class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
        </section>
    </div>
</div>
