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
 * @var \phpOMS\Views\View $this
 */
echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-9">
        <div id="testEditor" class="m-editor">
            <section class="portlet">
                <div class="portlet-body">
                    <input id="iTitle" type="text" name="title" form="docForm">
                </div>
            </section>

            <section class="portlet">
                <div class="portlet-body">
                    <?= $this->getData('editor')->render('iNews'); ?>
                </div>
            </section>

            <div class="box wf-100">
            <?= $this->getData('editor')->getData('text')->render('iNews', 'plain', 'docForm'); ?>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-md-3">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Status'); ?></div>
            <div class="portlet-body">
                <form id="docForm" method="PUT" action="<?= \phpOMS\Uri\UriFactory::build('{/api}news?{?}&csrf={$CSRF}'); ?>">
                    <table class="layout wf-100">
                        <tr><td colspan="2"><select name="status" id="iStatus">
                                    <option value="<?= $this->printHtml(Modules\News\Models\NewsStatus::DRAFT); ?>" selected><?= $this->getHtml('Draft'); ?>
                                    <option value="<?= $this->printHtml(Modules\News\Models\NewsStatus::VISIBLE); ?>"><?= $this->getHtml('Visible'); ?>
                        <tr>
                            <td colspan="2">
                                <label for="iPublish"><?= $this->getHtml('Publish'); ?></label>
                        <tr>
                            <td colspan="2">
                                <input type="datetime-local" name="publish" id="iPublish" value="<?= $this->printHtml((new \DateTime('NOW'))->format('Y-m-d\TH:i:s')); ?>">
                        <tr>
                            <td>
                                <input type="submit" name="deleteButton" id="iDeleteButton" value="<?= $this->getHtml('Delete', '0', '0'); ?>">
                            <td class="rightText">
                                <input type="submit" formaction="<?= \phpOMS\Uri\UriFactory::build('{/api}news?{?}&release=false&csrf={$CSRF}'); ?>" name="saveButton" id="iSaveButton" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                                <input type="submit" name="publishButton" id="iPublishButton" value="<?= $this->getHtml('Publish'); ?>">
                    </table>
                </form>
            </div>
        </section>
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Type'); ?></div>
            <div class="portlet-body">
                <table class="layout wf-100">
                    <tr><td>
                        <span class="radio">
                            <label for="iNewsTypeArticle"><?= $this->getHtml('News'); ?></label>
                            <input type="radio" name="type" id="iNewsTypeArticle" form="docForm" value="<?= $this->printHtml(Modules\News\Models\NewsType::ARTICLE); ?>" checked>
                            <span class="checkmark"></span>
                        </span>
                    <tr><td>
                        <span class="radio">
                            <label for="iNewsTypeHeadline"><?= $this->getHtml('Headline'); ?></label>
                            <input type="radio" name="type" id="iNewsTypeHeadline" form="docForm" value="<?= $this->printHtml(Modules\News\Models\NewsType::HEADLINE); ?>">
                            <span class="checkmark"></span>
                        </span>
                    <tr><td>
                        <span class="radio">
                            <label for="iNewsTypeLink"><?= $this->getHtml('Link'); ?></label>
                            <input type="radio" name="type" id="iNewsTypeLink" form="docForm" value="<?= $this->printHtml(Modules\News\Models\NewsType::LINK); ?>">
                            <span class="checkmark"></span>
                        </span>
                </table>
            </div>
        </section>
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Accounts/Groups'); ?></div>
            <div class="portlet-body">
                <table class="layout wf-100">
                    <!-- @todo add form this belongs to -->
                    <!-- @todo make auto save on change for already created news article -->
                    <!-- @todo add default values (some can be removed/overwritten and some not?) -->
                    <tr><td><?= $this->getData('accGrpSelector')->render('iReceiver', 'receiver', false); ?>
                </table>
            </div>
        </section>
    </div>
</div>
