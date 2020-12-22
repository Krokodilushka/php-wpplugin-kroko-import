<div class="wrap">
    <form action="<?php echo $currentUrl ?>" method="post">
        <table class="form-table" width="100%">
            <tr>
                <td align="left">
                    <a href="<?php echo $currentUrl ?>&drop_feeds=1">Сброс фидов</a>
                </td>
                <td align="right">
                    Новый xml url: <input type="text" name="xml_url" value="" size="100">
                    &nbsp;<input class="button-primary" name="new_xml" value="Добавить &raquo;" type="submit"><br/>
                </td>
            </tr>
        </table>
    </form>
    <form id="syndycated_feeds" action="#" method="post">
        <?php if (!empty($feeds)) { ?>
            <table class="widefat" style="margin-top: .5em" width="100%">
                <thead>
                    <tr>
                        <th scope="row" width="10%">Действия</th>
                        <th scope="row" width="25%">Имя</th>
                        <th scope="row" width="50%">URL</th>
                        <th scope="row" width="15%">Последнее обновление</th>
                        <th scope="row" width="15%">Обновление через мин</th>
                    </tr>
                </thead>
                <?php foreach ($feeds as $feed) { ?>
                    <tr>
                        <th align="center">[<a href="<?php echo $currentUrl ?>&feed_id=<?php echo $feed->getID() ?>">изменить / удалить</a>]</th>
                        <td><?php echo $feed->getTitle() ?></td>
                        <td><a href="<?php echo $feed->getUrl() ?>" target="_blank"><?php echo esc_url($feed->getUrl()) ?></a></td>
                        <td><?php echo (($feed->getLastUpdateTime() !== NULL) ? date_i18n("d.m.Y H:i:s", $feed->getLastUpdateTime()) : '-') ?></td>
                        <td>
                            <?php echo round($feed->leftUntilUpdateSec() / 60) ?><br/>
                            [<a href="<?php echo $currentUrl ?>&update_posts=<?php echo $feed->getID() ?>">обновить</a>]
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </form>
    <table class="widefat" style="margin-top: .5em;text-align: center" width="100%">
        <tr>
            <td>
                Ссылка для обновлений: <?php echo get_site_url() ?>/?<?php echo $magicKeyGETKey ?>=<?php echo $magicKey ?>
            </td>
        </tr>
    </table>
</div>