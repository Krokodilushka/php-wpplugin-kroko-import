<?php
/** @var $currentUrl string */
/** @var $cronNextTime int */
/** @var $cronExecutable string */

use KrokoImport\Constants;
use KrokoImport\View\Tables\FeedsTable;

$feedsTable = new FeedsTable();
if ( ! empty( $feeds ) ) {
	/** @var \KrokoImport\Data\FeedOptions $feed */
	foreach ( $feeds as $feed ) {
		$feedsTable->addItem( $feed->getID(), $feed->getTitle(), $feed->getUrl(), $feed->getLastUpdateTime(), $feed->leftUntilUpdateSec() );
	}
}
$feedsTable->prepareItems();
?>
    <div class="wrap">
        <form action="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query( [
			Constants::ROUTE_FEED        => true,
			Constants::ROUTE_FEED_CREATE => true
		] ) ?>"
              method="post">
            <table class="form-table" style="width:100%">
                <tr>
                    <td style="text-align: left">
                        <!--                    <a href="-->
						<?php //echo $currentUrl ?><!--&drop_feeds">Сброс фидов</a>-->
                    </td>
                    <td style="text-align: right">
                        Новый xml url: <input type="text" name="feed_url" value="" size="100">
                        &nbsp;<input class="button-primary" name="new_xml" value="Добавить &raquo;" type="submit"><br/>
                    </td>
                </tr>
            </table>
        </form>
		<?php
		$feedsTable->display();
		?>
        <table class="widefat" style="margin-top: .5em;text-align: center:width:100%">
            <tr>
                <td>
                    Время сейчас: <?= date( 'd.m.Y H:i:s' ); ?><br/>
                    Cron обработчик сработает через: <?= $cronNextTime - time() ?> сек
                    (<?= date( 'd.m.Y H:i:s', $cronNextTime ) ?>)<br/>
                    Команда для запуска крона: <?= $cronExecutable ?>
                </td>
            </tr>
        </table>
    </div>

<?php


?>