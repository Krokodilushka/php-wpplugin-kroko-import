<?php


namespace KrokoImport;


class Constants {
	const PLUGIN_URL_SLUG = 'kroko-import';

	const ROUTE_FEED = 'feed';
	const ROUTE_FEED_CREATE = 'feed_create';
	const ROUTE_FEED_UPDATE = 'feed_update';
	const ROUTE_FEED_DELETE = 'feed_delete';
	const ROUTE_FEED_SHOW_POSTS = 'feed_show_posts';
	const ROUTE_FEED_SAVE = 'feed_save';
	const ROUTE_FEED_DROP_ALL = 'feed_drop_all';

	const ROUTE_IMPORT = 'import';
	const ROUTE_IMPORT_MANUAL = 'manual';
	const ROUTE_IMPORT_POST = 'post';

	const CRON_NEW_POST_HOOK_NAME = 'wp_krokoimport_new_post';
	const CRON_INTERVAL_SEC = 600;
}