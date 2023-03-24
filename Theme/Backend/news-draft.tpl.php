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

use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
/** @var \Modules\News\Models\NewsArticle[] $newsList */
$newsList = $this->getData('news') ?? [];

$previous = empty($newsList) ? 'news/archive' : 'news/archive?{?}&id=' . \reset($newsList)->getId() . '&ptype=p';
$next     = empty($newsList) ? 'news/archive' : 'news/archive?{?}&id=' . \end($newsList)->getId() . '&ptype=n';

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Draft'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <div class="slider">
            <table id="newsArchiveList" class="default sticky">
                <thead>
                <tr>
                    <td><?= $this->getHtml('Type'); ?>
                        <label for="newsArchiveList-sort-1">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-1">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsArchiveList-sort-2">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-2">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Title'); ?>
                        <label for="newsArchiveList-sort-3">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-3">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsArchiveList-sort-4">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-4">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Author'); ?>
                        <label for="newsArchiveList-sort-5">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-5">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsArchiveList-sort-6">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-6">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Date'); ?>
                        <label for="newsArchiveList-sort-7">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-7">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="newsArchiveList-sort-8">
                            <input type="radio" name="newsArchiveList-sort" id="newsArchiveList-sort-8">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
            <tbody>
                <?php
                    $count = 0;

                    foreach ($newsList as $key => $news) : ++$count;
                    $url   = UriFactory::build('{/base}/news/edit?{?}&id=' . $news->getId());
                ?>
                    <tr tabindex="0" data-href="<?= $url; ?>">
                        <td><span class="tag"><?= $this->getHtml('TYPE' . $news->getType()); ?></span></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($news->title); ?></a>
                        <td><a class="content" href="<?= UriFactory::build('{/base}/profile/single?{?}&for=' . $news->createdBy->getId()); ?>"><?= $this->printHtml($news->createdBy->name2 . ', ' . $news->createdBy->name1); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($news->publish->format('Y-m-d')); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="4" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            </div>
            <div class="portlet-foot">
                <a class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
        </section>
    </div>
</div>
