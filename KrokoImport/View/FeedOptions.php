<?php
/** @var $alerts array|null */
/** @var $xmlUrl string */
/** @var $title string|null */
/** @var $saveAtOnce int|null */
/** @var $intervalMin int|null */
/** @var $onExistsUpdate bool */
?>
<?php if (isset($alerts) && !empty($alerts)) { ?>
    <?php foreach ($alerts as $value) { ?>
        <div class="alert"><?php echo $value; ?></div>
    <?php } ?>
<?php } ?>
<form action="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query([\KrokoImport\Constants::ROUTE_FEED => true, \KrokoImport\Constants::ROUTE_FEED_SAVE => true]) ?>"
      method="post">
    <table class="form-table">
        <tbody>
        <?php if (isset($feedId)) { ?>
            <tr>
                <th scope="row">ID:</th>
                <td><input type="text" name="feed_id" value="<?php echo $feedId ?>" size="5" readonly></td>
            </tr>
        <?php } ?>
        <tr>
            <th scope="row">XML URL:</th>
            <td>
                <input type="text" name="feed_url" size="100" value="<?php echo esc_url($xmlUrl) ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Название фида:</th>
            <td>
                <input type="text" name="feed_title" size="70" value="<?php echo $title ?: ''; ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Сколько постов создавать за раз (0 - хоть сколько):</th>
            <td>
                <input type="text" name="feed_save_at_once" size="70" value="<?php echo $saveAtOnce ?: 0; ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Период обновления в мин (0 - каждый cron вызов):</th>
            <td>
                <input type="number" name="feed_interval_min" size="5" value="<?php echo $intervalMin ?: '0'; ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Обновлять посты (заодно и комментарии):</th>
            <td>
                <input type="checkbox"
                       name="feed_on_exists_update"<?php if ($onExistsUpdate) { ?> checked="checked"<?php } ?>/>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="submit">
        <input class="button-primary" name="update_feed_settings" value="Сохранить" type="submit">
    </div>
</form>
<?php if (isset($feedId) && !is_null($feedId)) { ?>
    <form
            action="<?= \KrokoImport\Route::pluginUrlPath() . '&' . http_build_query([\KrokoImport\Constants::ROUTE_FEED => true, \KrokoImport\Constants::ROUTE_FEED_DELETE => true]) ?>"
            method="POST">
        <input type="hidden" name="feed_id" value="<?= $feedId ?>">
        <input class="button-primary" value="Удалить" type="submit">
    </form>
<?php } ?>
<div class="feeds-data">
    <?php if (isset($feedData) && $feedData !== NULL) { ?>
        <?php if ($feedData->countPosts() > 0) { ?>
            <?php foreach ($feedData->getPosts() as $value) { ?>
                ID: <?php echo $value->getID() ?><br/>
                Title: <?php echo $value->getTitle() ?><br/>
                Post content: <?php echo $value->getContent() ?><br/>
                Thumbnail: <?php echo $value->getThumbnail() ?><br/>
                Categories: <?php echo $value->getCategories()->toString() ?><br/>
                Tags: <?php echo $value->getTags()->toString() ?><br/>
                Metas: <?php echo $value->getMetas()->toString() ?><br/>
                Comments: <?php echo $value->getComments()->count() ?><br/>
                <hr/>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</div>