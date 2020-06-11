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

use phpOMS\Uri\UriFactory;

/** @var \Modules\News\Models\NewsArticle $news */
$news = $this->getData('news');

/** @var bool $editable */
$editable = $this->getData('editable');

/** @var \Modules\Tag\Models\Tag[] $tag */
$tags = $news->getTags();

/** @var \phpOMS\Views\View $this */
echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <article>
                <h1><?= $this->printHtml($news->getTitle()); ?></h1>
                <?= $news->getContent(); ?>
            </article>
            <?php if ($editable || !empty($tags)) : ?>
            <div class="portlet-foot">
                <div class="row">
                    <div class="col-xs-6 overflowfix">
                        <?php foreach ($tags as $tag) : ?>
                            <span class="tag" style="background: <?= $this->printHtml($tag->getColor()); ?>"><?= $this->printHtml($tag->getTitle()); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($editable) : ?>
                    <div class="col-xs-6 end-xs plain-grid">
                        <a tabindex="0" class="button" href="<?= UriFactory::build('{/prefix}news/edit?id=' . $news->getId()); ?>">Edit</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
