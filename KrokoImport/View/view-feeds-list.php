<?php
/** @var $currentUrl string */
/** @var $cron_next_time int */

/** @var $cron_executable string */

use KrokoImport\Constants;
use KrokoImport\View\Tables\Feeds_Table;

$feedsTable = new Feeds_Table();
if ( ! empty( $feeds ) ) {
	/** @var \KrokoImport\Data\Feed_Options $feed */
	foreach ( $feeds as $feed ) {
		$feedsTable->add_item( $feed->get_id(), $feed->get_title(), $feed->get_url(), $feed->get_last_update_time(), $feed->left_until_update_sec() );
	}
	$feedsTable->prepare_items();
}
?>
    <div class="wrap">
        <table class="form-table" style="width:100%">
            <tr>
                <td style="text-align: right">
                    <form action="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query( [
						Constants::ROUTE_FEED        => true,
						Constants::ROUTE_FEED_CREATE => true
					] ) ?>"
                          method="post">
                        Новый xml url: <input type="text" name="feed_url" value="" size="100"/>
                        &nbsp;<input class="button-primary" name="new_xml" value="Добавить &raquo;" type="submit">
                    </form>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <form action="<?= \KrokoImport\Route::pluginUrlPath() ?>" method="get">
                        <input type="hidden" name="page" value="<?= $_REQUEST['page'] ?>">
                        <input type="hidden" name="<?= Constants::ROUTE_FEED ?>" value="1">
                        <input type="hidden" name="<?= Constants::ROUTE_FEED_SHOW_POSTS ?>" value="1">
                        Просмотр фида: <input type="text" name="feed_url" value="" size="100"/>
                        &nbsp;<input class="button-primary" name="new_xml" value="Добавить &raquo;" type="submit">
                    </form>
                </td>
            </tr>
        </table>
		<?php
		$feedsTable->display();
		?>
        <table class="widefat" style="margin-top: .5em;text-align: center:width:100%">
            <tr>
                <td>
                    Время сейчас: <?= date( 'd.m.Y H:i:s' ); ?><br/>
                    Cron обработчик сработает через: <?= $cron_next_time - time() ?> сек
                    (<?= date( 'd.m.Y H:i:s', $cron_next_time ) ?>)<br/>
                    Команда для запуска крона: <?= $cron_executable ?>
                </td>
            </tr>
        </table>
    </div>

<?php


?>