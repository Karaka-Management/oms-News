<?php
/**
 * Jingga
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

use Modules\News\Models\NewsStatus;
use Modules\News\Models\NewsType;
use Modules\News\Models\NullNewsArticle;
use phpOMS\Uri\UriFactory;

/** @var \Modules\News\Models\NewsArticle $news */
$news         = $this->getData('news') ?? new NullNewsArticle();
$isNewArticle = $news->id === 0;
$languages    = \phpOMS\Localization\ISO639Enum::getConstants();

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-9">
        <div id="testEditor" class="m-editor">
            <section class="portlet">
                <div class="portlet-body">
                    <input id="iTitle" type="text" name="title" form="docForm" value="<?= $news->title; ?>" autocomplete="off">
                </div>
            </section>

            <section class="portlet">
                <div class="portlet-body">
                    <?= $this->getData('editor')->render('iNews'); ?>
                </div>
            </section>

            <section class="portlet">
                <div class="portlet-body">
                    <?= $this->getData('editor')->getData('text')->render('iNews', 'plain', 'docForm', $news->plain, $news->content); ?>
                </div>
            </section>
        </div>
    </div>

    <div class="col-xs-12 col-md-3">
        <section class="portlet">
            <form id="docForm"
                method="<?= $isNewArticle ? 'PUT' : 'POST'; ?>"
                action="<?= UriFactory::build('{/api}news?' . ($isNewArticle ? '' : 'id={?id}&') . 'csrf={$CSRF}'); ?>">
                <div class="portlet-head"><?= $this->getHtml('Status'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <select name="status" id="iStatus">
                            <option value="<?= NewsStatus::DRAFT; ?>"<?= $news->getStatus() === NewsStatus::DRAFT ? ' selected' : ''; ?>><?= $this->getHtml('Draft'); ?>
                            <option value="<?= NewsStatus::VISIBLE; ?>"<?= $news->getStatus() === NewsStatus::VISIBLE ? ' selected' : ''; ?>><?= $this->getHtml('Visible'); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="iPublish"><?= $this->getHtml('Publish'); ?></label>
                        <input type="datetime-local" name="publish" id="iPublish" value="<?= $this->printHtml($news->publish->format('Y-m-d\TH:i:s')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="iLanguages"><?= $this->getHtml('Language'); ?></label>
                        <select id="iLanguages" name="lang">
                            <?php foreach ($languages as $code => $language) : $code = \strtolower(\substr($code, 1)); ?>
                            <option value="<?= $this->printHtml($code); ?>"<?= $this->printHtml($code === $news->getLanguage() ? ' selected' : ''); ?>><?= $this->printHtml($language); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="checkbox" for="iComment">
                            <input id="iComment" type="checkbox" name="allow_comments" value="1">
                            <span class="checkmark"></span>
                            <?= $this->getHtml('AllowComments'); ?>
                        </label>
                    </div>
                </div>
                <div class="portlet-foot">
                    <table class="layout wf-100">
                        <tr>
                            <td>
                                <?php if ($isNewArticle) : ?>
                                    <a href="<?= UriFactory::build('{/base}//news/dashboard'); ?>" class="button"><?= $this->getHtml('Delete', '0', '0'); ?></a>
                                <?php else : ?>
                                    <input type="submit" name="deleteButton" id="iDeleteButton" value="<?= $this->getHtml('Delete', '0', '0'); ?>">
                                <?php endif; ?>
                            <td class="rightText">
                                <input type="submit" name="saveButton" id="iSaveButton" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                    </table>
                </div>
            </form>
        </section>
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Categories'); ?></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label class="radio" for="iNewsTypeArticle">
                        <input type="radio" name="type" id="iNewsTypeArticle" form="docForm" value="<?= NewsType::ARTICLE; ?>"<?= $news->getType() === NewsType::ARTICLE ? ' checked' : ''; ?>>
                        <span class="checkmark"></span>
                        <?= $this->getHtml('News'); ?>
                    </label>
                </div>

                <div class="form-group">
                    <label class="radio" for="iNewsTypeHeadline">
                        <input type="radio" name="type" id="iNewsTypeHeadline" form="docForm" value="<?= NewsType::HEADLINE; ?>"<?= $news->getType() === NewsType::HEADLINE ? ' checked' : ''; ?>>
                        <span class="checkmark"></span>
                        <?= $this->getHtml('Headline'); ?>
                    </label>
                </div>

                <div class="form-group">
                    <label class="radio" for="iNewsTypeLink">
                        <input type="radio" name="type" id="iNewsTypeLink" form="docForm" value="<?= NewsType::LINK; ?>"<?= $news->getType() === NewsType::LINK ? ' checked' : ''; ?>>
                        <span class="checkmark"></span>
                        <?= $this->getHtml('Link'); ?>
                    </label>
                </div>

                <div class="form-group">
                    <?= $this->getHtml('Tags', 'Tag'); ?>
                    <?= $this->getData('tagSelector')->render('iTag', 'tag', 'fEditor', false); ?>
                </div>
            </div>
        </section>
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Accounts/Groups'); ?></div>
            <div class="portlet-body">
                <div class="form-group">
                    <!-- @todo add form this belongs to -->
                    <!-- @todo make auto save on change for already created news article -->
                    <!-- @todo add default values (some can be removed/overwritten and some not?) -->
                    <?= $this->getData('accGrpSelector')->render('iReceiver', 'receiver', false); ?>
                </div>
            </div>
        </section>
    </div>
</div>
