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

use Modules\Comments\Models\CommentListStatus;
use phpOMS\Uri\UriFactory;

/** @var \Modules\News\Models\NewsArticle $news */
$news = $this->getData('news');

/** @var bool $editable */
$editable = $this->getData('editable');

/** @var \Modules\Tag\Models\Tag[] $tags */
$tags = $news->getTags();

$profile = UriFactory::build('{/prefix}profile/single?{?}&id=' . $news->createdBy->getId());

/** @var \phpOMS\Views\View $this */
echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <article>
                <h1><?= $this->printHtml($news->title); ?><span class="floatRight"><a href="<?= $profile; ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$news->createdBy->name1, $news->createdBy->name2, $news->createdBy->name3, $news->createdBy->login ?? ''])); ?></a> - <?= $news->publish->format('Y-m-d'); ?></span></h1>
                <?= $news->content; ?>
            </article>
            <?php if ($editable || !empty($tags)) : ?>
            <div class="portlet-foot">
                <div class="row">
                    <div class="col-xs-6 overflowfix">
                        <?php foreach ($tags as $tag) : ?>
                            <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= $tag->icon !== null ? '<i class="' . $this->printHtml($tag->icon ?? '') . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                        <?php endforeach; ?>

                        <?php $files = $news->getMedia(); foreach ($files as $file) : ?>
                            <span><a class="content" href="<?= UriFactory::build('{/prefix}media/single?id=' . $file->getId());?>"><?= $file->name; ?></a></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($editable) : ?>
                    <div class="col-xs-6 end-xs plain-grid">
                        <a tabindex="0" class="button" href="<?= UriFactory::build('{/prefix}news/edit?id=' . $news->getId()); ?>"><?= $this->getHtml('Edit'); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php
$commentList = $news->comments;
if (!empty($commentList) && $commentList->status !== CommentListStatus::INACTIVE) :
    /* @todo: check if user has permission to create a comment here, maybe he is only allowed to read comments */
    echo $this->getData('commentCreate')->render(1);
    echo $this->getData('commentList')->render($commentList);
endif; ?>
