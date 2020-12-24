<?php


namespace KrokoImport;


class Constants
{
    const PLUGIN_URL_SLUG = 'kroko-import';
    const CRON_UPDATE_GET_KEY_NAME = 'kroko_import_update';
    const CRON_UPDATE_MAGIC_KEY = 'kroko_import_magic';
    const CRON_MAX_FEED_UPDATE_AT_ONCE = 1;

    const ROUTE_FEED = 'feed';
    const ROUTE_FEED_CREATE = 'feed_create';
    const ROUTE_FEED_UPDATE = 'feed_update';
    const ROUTE_FEED_DELETE = 'feed_delete';
    const ROUTE_FEED_SAVE = 'feed_save';
    const ROUTE_FEED_DROP_ALL = 'feed_drop_all';

    const ROUTE_IMPORT = 'import';
    const ROUTE_IMPORT_MANUAL = 'manual';
}