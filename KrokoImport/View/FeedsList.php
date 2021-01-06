<?php
/** @var $feeds \KrokoImport\Data\XML\Feed */
/** @var $currentUrl string */

/** @var $cronNextTime int */

/** @var $cronExecutable string */

use KrokoImport\Constants;

$dt1 = new DateTime("@0");

?>
<div class="wrap">
    <form action="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query([Constants::ROUTE_FEED => true, Constants::ROUTE_FEED_CREATE => true]) ?>"
          method="post">
        <table class="form-table" style="width:100%">
            <tr>
                <td style="text-align: left">
                    <!--                    <a href="--><?php //echo $currentUrl ?><!--&drop_feeds">Сброс фидов</a>-->
                </td>
                <td style="text-align: right">
                    Новый xml url: <input type="text" name="feed_url" value="" size="100">
                    &nbsp;<input class="button-primary" name="new_xml" value="Добавить &raquo;" type="submit"><br/>
                </td>
            </tr>
        </table>
    </form>
    <form id="syndycated_feeds" action="#" method="post">
        <?php if (!empty($feeds)) { ?>
            <table class="widefat" style="margin-top: .5em;width:100%">
                <thead>
                <tr>
                    <th scope="row" style="width:15%">Действия</th>
                    <th scope="row" style="width:5%">id</th>
                    <th scope="row" style="width:20%">Имя</th>
                    <th scope="row" style="width:45%">URL</th>
                    <th scope="row" style="width:15%">Последнее обновление</th>
                    <th scope="row" style="width:15%">Обновление через</th>
                </tr>
                </thead>
                <?php foreach ($feeds as $feed) { ?>
                    <tr>
                        <th style="text-align: center">
                            [
                            <a href="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query([Constants::ROUTE_FEED => true, Constants::ROUTE_FEED_UPDATE => true, 'feed_id' => $feed->getId()]) ?>">
                                изменить / удалить
                            </a>
                            ]
                            <br/>
                            [
                            <a href="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query([Constants::ROUTE_IMPORT => true, Constants::ROUTE_IMPORT_MANUAL => true, 'feed_id' => $feed->getId()]) ?>">
                                импортировать посты
                            </a>
                            ]
                        </th>
                        <td><?= $feed->getId() ?></td>
                        <td><?= $feed->getTitle() ?></td>
                        <td>
                            <a href="<?= $feed->getUrl() ?>" target="_blank"><?= esc_url($feed->getUrl()) ?></a>
                        </td>
                        <td><?= (($feed->getLastUpdateTime() !== NULL) ? date_i18n("d.m.Y H:i:s", $feed->getLastUpdateTime()) : '-') ?></td>
                        <td style="text-align: center">
                            <?= $dt1->diff(new DateTime("@" . $feed->leftUntilUpdateSec()))->format('%a д<br/>%h:%i:%s'); ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </form>
    <table class="widefat" style="margin-top: .5em;text-align: center:width:100%">
        <tr>
            <td>
                Время сейчас: <?= date('d.m.Y H:i:s'); ?><br/>
                Cron обработчик сработает через: <?= $cronNextTime - time() ?> сек
                (<?= date('d.m.Y H:i:s', $cronNextTime) ?>)<br/>
                Команда для запуска крона: <?= $cronExecutable ?>
            </td>
        </tr>
    </table>
</div>