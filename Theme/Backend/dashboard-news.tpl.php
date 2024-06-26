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

use Modules\News\Models\NewsType;
use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */

$newsList = $this->data['news'] ?? [];
?>
<div id="news-dashboard" class="col-xs-12 col-md-6" draggable="true">
    <div class="portlet">
        <div class="portlet-head"><?= $this->getHtml('News', 'News'); ?></div>
        <div class="slider">
        <table class="default sticky">
            <thead>
            <tr>
                <td>
                <td><?= $this->getHtml('Type', 'News'); ?>
                <td class="wf-100"><?= $this->getHtml('Title', 'News'); ?>
            <tbody>
            <?php
            $count = 0;
            foreach ($newsList as $key => $news) : ++$count;
            $url   = UriFactory::build('{/base}/news/article?{?}&id=' . $news->id);
            $color = 'darkred';

            if ($news->type === NewsType::ARTICLE) { $color = 'green'; }
            elseif ($news->type === NewsType::HEADLINE) { $color = 'purple'; }
            elseif ($news->type === NewsType::LINK) { $color = 'yellow'; }
            ?>
            <tr data-href="<?= $url; ?>">
                <td data-label="">
                    <?php if ($news->isFeatured) : ?>
                        <a href="<?= $url; ?>">
                            <i class="g-icon favorite">star</i>
                        </a>
                    <?php endif; ?>
                <td data-label="<?= $this->getHtml('Type', 'News'); ?>">
                    <a href="<?= $url; ?>">
                        <span class="tag <?= $this->printHtml($color); ?>">
                            <?= $this->getHtml(':TYPE' . $news->type, 'News'); ?>
                        </span>
                    </a>
                <td data-label="<?= $this->getHtml('Title', 'News'); ?>">
                    <a href="<?= $url; ?>">
                        <?= $this->printHtml($news->title); ?>
                    </a>
                    <?php endforeach; ?>
                    <?php if ($count === 0) : ?>
            <tr><td colspan="5" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                    <?php endif; ?>
        </table>
        </div>
        <div class="portlet-foot">
            <a class="button" href="<?= UriFactory::build('{/base}/news/dashboard?{?}'); ?>"><?= $this->getHtml('More', '0', '0'); ?></a>
        </div>
    </div>
</div>