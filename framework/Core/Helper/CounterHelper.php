<?php

namespace Dore\Core\Helper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class CounterHelper implements ExtensionInterface
{

    protected $db;
    protected $cache;

    public function __construct($db, $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('online', [$this, 'userAllOnline']);
        $engine->registerFunction('usersOnline', [$this, 'usersOnline']);
        $engine->registerFunction('guestaOnline', [$this, 'guestaOnline']);
    }

    /**
     * Counter online
     * @return int
     */
    public function userAllOnline()
    {
        $result = $this->cache->get('allOnline');
        if ($result == false) {
            $sql    = 'SELECT COUNT(*) AS `count` FROM `sessions` WHERE `timestamp` > ' . (time() - 300);
            $STH    = $this->db->prepare($sql);
            $STH->execute();
            $count  = $STH->fetch(\PDO::FETCH_OBJ)->count;
            $result = ($count < 1 ) ? 1 : $count;
            $this->cache->set('allOnline', $result, 60);
        }
        return $result;
    }

    /**
     * Counter Online visitors
     * @return int
     */
    public function usersOnline()
    {
        $sql   = 'SELECT COUNT(*) AS `count` FROM `sessions` WHERE `userId` > 0 AND `timestamp` > ' . (time() - 300);
        $STH   = $this->db->prepare($sql);
        $STH->execute();
        $count = $STH->fetch(\PDO::FETCH_OBJ)->count;
        return ($count < 1) ? 1 : $count;
    }

    /**
     * Counter guests online
     * @return int
     */
    public function guestaOnline()
    {
        $sql   = 'SELECT COUNT(*) AS `count` FROM `sessions` WHERE `userId` = 0 AND `timestamp` > ' . (time() - 300);
        $STH   = $this->db->prepare($sql);
        $STH->execute();
        $count = $STH->fetch(\PDO::FETCH_OBJ)->count;
        return ($count < 1) ? 1 : $count;
    }

}
