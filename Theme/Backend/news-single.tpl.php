<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
$news = $this->data['news'];

/** @var bool $editable */
$editable = $this->data['editable'];

$profile = UriFactory::build('profile/view?{?}&id=' . $news->createdBy->id);

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-body">
                <article>
                    <h1>
                        <span class="rf">
                            <a href="<?= $profile; ?>">
                                <?= $this->printHtml($this->renderUserName(
                                    '%3$s %2$s %1$s',
                                    [$news->createdBy->name1, $news->createdBy->name2, $news->createdBy->name3, $news->createdBy->login ?? '']
                                )); ?>
                            </a> - <?= $news->publish->format('Y-m-d'); ?>
                        </span><?= $this->printHtml($news->title); ?>
                    </h1>

                    <?= $news->content; ?>
                </article>
                <div>
                    <?php $files = $news->files; foreach ($files as $file) : ?>
                        <span><a class="content" href="<?= UriFactory::build('{/base}/media/view?id=' . $file->id);?>"><?= $file->name; ?></a></span>
                    <?php endforeach; ?>
                </div>
                <div class="tag-list">
                    <?php foreach ($news->tags as $tag) : ?>
                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>">
                            <?= $this->printHtml($tag->getL11n()); ?>
                        </span>
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
if ($this->data['commentPermissions']['write'] && $commentList->status === CommentListStatus::ACTIVE) :
  echo $this->getData('commentCreate')->render(1);
endif;

if ($this->data['commentPermissions']['list_modify']
    || ($this->data['commentPermissions']['list_read'] && $commentList->status !== CommentListStatus::INACTIVE)
) :
    echo $this->getData('commentList')->render($commentList);
endif;
