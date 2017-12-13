<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 05.12.2017
 */

namespace Libs\notifications;


class NotificationsModel
{
    protected $manager;
    protected $uid;

    public function __construct()
    {
        $db = new \Libs\Db();
        $this->manager = $db->GetManager();

        $this->uid = time();
    }

    public function getNotifications()
    {
        static $a = [];

        if(!isset($a[$this->uid])) {
            $a = [];

            $sql = 'select hash from table_notifications where created_at>now()-interval 1 HOUR';
            $statement = $this->manager->getConnection()->prepare($sql);
            $statement->execute();
            $items = $statement->fetchAll();

            foreach ($items as $item) {
                $a[$this->uid][$item['hash']] = true;
            }

        }

        if(isset($a[$this->uid])) {
            return $a[$this->uid];
        }
        else {
            return false;
        }
    }

    /**
     * Проверяет есть ли такой хэш
     * @param $hash
     * @return bool
     */
    public function existNotification($hash) {
        $items = $this->getNotifications();

        if(isset($items[$hash])) {
            return true;
        }
        else {
            return false;
        }
    }

}