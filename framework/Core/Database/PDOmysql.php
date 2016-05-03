<?php

namespace Dore\Core\Database;

use Dore\Core\Foundation\App;

/**
 * Class PDOmysql
 * @package Dore\Core\Database
 */
class PDOmysql extends \PDO
{

    /**
     * PDOmysql constructor.
     *
     * @param string $sql
     */
    public function __construct($sql = 'default')
    {
        try {
            $connection = App::config()->get("db.{$sql}.connection");
            $user = App::config()->get("db.{$sql}.user");
            $password = App::config()->get("db.{$sql}.password");
            parent::__construct($connection, $user, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
            ]);

        } catch (\PDOException $e) {
            throw new DbException("DB Error: [{$e->getMessage()}]");
        }
    }

}
