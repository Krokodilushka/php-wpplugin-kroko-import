<?php


namespace KrokoImport\Controller;


use KrokoImport\Constants;

class Index_Controller extends Controller
{
    public function list_feeds(): string
    {
        $cron_next_time = wp_next_scheduled(Constants::CRON_NEW_POST_HOOK_NAME, []);
        $cron_executable = 'cd '.$_SERVER['DOCUMENT_ROOT'] . ' && ' . $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/kroko-import/cron.sh';
        return $this->get_holder()->get_view()->get('view-feeds-list', array(
            'feeds' => $this->get_holder()->get_feed_storage()->get_all(),
            'cron_next_time' => $cron_next_time,
            'cron_executable' => $cron_executable,
        ));
    }

}