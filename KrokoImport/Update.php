<?php


namespace KrokoImport;


use KrokoImport\Model\Holder;

class Update
{
    private $_holder;

    public function __construct()
    {
        $this->_holder = new Holder;
    }

    // это запуск cron?
    public function checkUpdatesByCron()
    {
        $magicKey = filter_input(INPUT_GET, Constants::CRON_UPDATE_GET_KEY_NAME);
        if ($magicKey == self::getCronMagicKey()) {
            echo '<pre>';
            $feeds = $this->_holder->getFeedStorage()->getAll();
            if (!empty($feeds)) {
                usort($feeds, function ($objectA, $objectB) {
                    return $objectA->leftUntilUpdateSec() > $objectB->leftUntilUpdateSec();
                });
                $i = 0;
                foreach ($feeds as $feed) {
                    echo 'обновление по фиду ID ' . $feed->getID() . ' осталось до обновления ' . round($feed->leftUntilUpdateSec() / 60) . " min \n";
                    if ($feed->leftUntilUpdateSec() == 0) {
                        try {
                            $this->_holder->getFeedStorage()->perform($feed);
                            echo "логи:\n";
                            print_r($this->_holder->getFeedStorage()->getLogs());
                            $this->_holder->getFeedStorage()->clearLogs();
                        } catch (\Exception $e) {
                            echo 'ошибка ' . $e->getMessage() . "\n";
                        }
                        $this->_holder->getFeedStorage()->setLastUpdateTime($feeds[0]->getID(), time());
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