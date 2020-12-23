<?php


namespace KrokoImport;


class Update
{

    // это запуск cron?
    public function checkUpdatesByCron()
    {
        $magicKey = filter_input(INPUT_GET, Constants::CRON_UPDATE_GET_KEY_NAME);
        if ($magicKey == self::getCronMagicKey()) {
            echo '<pre>';
            $feeds = $this->_feedStorage->getAll();
            if (!empty($feeds)) {
                usort($feeds, function ($objectA, $objectB) {
                    return $objectA->leftUntilUpdateSec() > $objectB->leftUntilUpdateSec();
                });
                $i = 0;
                foreach ($feeds as $feed) {
                    echo 'обновление по фиду ID ' . $feed->getID() . ' осталось до обновления ' . round($feed->leftUntilUpdateSec() / 60) . " min \n";
                    if ($feed->leftUntilUpdateSec() == 0) {
                        try {
                            $this->_importPosts->perform($feed);
                            echo "логи:\n";
                            print_r($this->_importPosts->getLogs());
                            $this->_importPosts->clearLogs();
                        } catch (\Exception $e) {
                            echo 'ошибка ' . $e->getMessage() . "\n";
                        }
                        $this->_feedStorage->setLastUpdateTime($feeds[0]->getID(), time());
                    }
                    $i++;
                    if ($i == Constants::CRON_MAX_FEED_UPDATE_AT_ONCE) {
                        break;
                    }
                }
            } else {
                echo "нет фидов\n";
            }
            echo '</pre>';
            wp_die();
        }
    }
}