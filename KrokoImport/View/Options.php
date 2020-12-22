<?php
/** @var $feeds \KrokoImport\Data\XML\Feed */
/** @var $currentUrl string */
/** @var $magicKeyGETKey string */
/** @var $magicKey string */
?>
<div class="wrap">
    <form action="<?php echo $currentUrl ?>" method="post">
        <table class="form-table" style="width:100%">
            <tr>
                <td style="text-align: left">
                    <a href="<?php echo $currentUrl ?>&drop_feeds=1">Сброс фидов</a>
                </td>
                <td style="text-align: right">
                    Новый xml url: <input type="text" name="xml_url" value="" size="100">
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
                    <th scope="row" style="width:10%">Действия</th>
                    <th scope="row" style="width:25%">Имя</th>
                    <th scope="row" style="width:50%">URL</th>
                    <th scope="row" style="width:15%">Последнее обновление</th>
                    <th scope="row" style="width:15%">Обновление через мин</th>
                </tr>
                </thead>
                <?php foreach ($feeds as $feed) { ?>
                    <tr>
                        <th style="text-align: center">
                            [
                            <a href="<?= $currentUrl ?>&feed_id=<?= $feed->getID() ?>">
                                изменить / удалить
                            </a>
                            ]
                        </th>
                        <td><?= $feed->getTitle() ?></td>
                        <td><a href="<?= $feed->getUrl() ?>"
                               target="_blank"><?= esc_url($feed->getUrl()) ?></a></td>
                        <td><?=(($feed->getLastUpdateTime() !== NULL) ? date_i18n("d.m.Y H:i:s", $feed->getLastUpdateTime()) : '-') ?></td>
                        <td>
                            <?= round($feed->leftUntilUpdateSec() / 60) ?><br/>
                            [<a href="<?= $currentUrl ?>&update_posts=<?= $feed->getID() ?>">обновить</a>]
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </form>
    <table class="widefat" style="margin-top: .5em;text-align: center:width:100%">
        <tr>
            <td>
                Ссылка для обновлений: <?php echo get_site_url() ?>/?<?= $magicKeyGETKey ?>
                =<?php echo $magicKey ?>
            </td>
        </tr>
    </table>
</div>