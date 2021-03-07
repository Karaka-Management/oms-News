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

$previous = empty($newsList) ? '{/prefix}news/draft/list' : '{/prefix}news/draft/list?{?}&id=' . \reset($newsList)->getId() . '&ptype=p';
$next     = empty($newsList) ? '{/prefix}news/draft/list' : '{/prefix}news/draft/list?{?}&id=' . \end($newsList)->getId() . '&ptype=n';

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Draft'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table id="newsDraftList" class="default">
                <thead>
                <tr>
                    <td><?= $this->getHtml('Type'); ?>
                        <label for="newsDraftList-sort-1">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-1">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsDraftList-sort-2">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-2">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Title'); ?>
                        <label for="newsDraftList-sort-3">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-3">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsDraftList-sort-4">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-4">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Author'); ?>
                        <label for="newsDraftList-sort-5">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-5">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsDraftList-sort-6">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-6">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Date'); ?>
                        <label for="newsDraftList-sort-7">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-7">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsDraftList-sort-8">
                            <input type="radio" name="newsDraftList-sort" id="newsDraftList-sort-8">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
            <tbody>
                <?php $count                                                  = 0; foreach ($articles as $key => $news) : ++$count; $url                                                  = UriFactory::build('{/prefix}news/edit?{?}&id=' . $news->getId());
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
