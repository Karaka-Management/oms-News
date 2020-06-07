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


/**
 * @var \Modules\News\Models\NewsArticle $news
 */
$news = $this->getData('news');

/**
 * @var bool $editable
 */
$editable = $this->getData('editable');

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <article>
                <h1><?= $this->printHtml($news->getTitle()); ?></h1>
                <?= $news->getContent(); ?>
            </article>
            <div class="portlet-foot">
                <div class="overflowfix">
                    <?php $tags = $news->getTags(); foreach ($tags as $tag) : ?>
                        <span class="tag" style="background: <?= $this->printHtml($tag->getColor()); ?>"><?= $this->printHtml($tag->getTitle()); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php if ($editable) : ?>
<div class="row">
    <div class="box">
        <a tabindex="0" class="button" href="<?= \phpOMS\Uri\UriFactory::build('{/prefix}news/edit?id=' . $news->getId()); ?>">Edit</a>
    </div>
</div>
<?php endif; ?>
