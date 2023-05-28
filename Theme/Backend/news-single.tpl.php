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

use Modules\Comments\Models\CommentListStatus;
use phpOMS\Uri\UriFactory;

/** @var \Modules\News\Models\NewsArticle $news */
$news = $this->getData('news');

/** @var bool $editable */
$editable = $this->getData('editable');

/** @var \Modules\Tag\Models\Tag[] $tags */
$tags = $news->getTags();

$profile = UriFactory::build('profile/single?{?}&id=' . $news->createdBy->id);

/** @var \phpOMS\Views\View $this */
echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-body">
                <article>
                    <h1><?= $this->printHtml($news->title); ?><span class="floatRight"><a href="<?= $profile; ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$news->createdBy->name1, $news->createdBy->name2, $news->createdBy->name3, $news->createdBy->login ?? ''])); ?></a> - <?= $news->publish->format('Y-m-d'); ?></span></h1>
                    <?= $news->content; ?>
                </article>
                <div>
                    <?php $files = $news->getMedia(); foreach ($files as $file) : ?>
                        <span><a class="content" href="<?= UriFactory::build('{/base}/media/single?id=' . $file->id);?>"><?= $file->name; ?></a></span>
                    <?php endforeach; ?>
                </div>
                <div>
                    <?php foreach ($tags as $tag) : ?>
                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= !empty($tag->icon) ? '<i class="' . $this->printHtml($tag->icon) . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($editable) : ?>
            <div class="portlet-foot">
                <a tabindex="0" class="button" href="<?= UriFactory::build('{/base}/news/edit?id=' . $news->id); ?>"><?= $this->getHtml('Edit'); ?></a>
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
