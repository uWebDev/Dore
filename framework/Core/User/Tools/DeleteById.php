<?php

namespace Dore\Core\User\Tools;

/**
 * Class DeleteById
 * @package Dore\Core\User\Tools
 */
class DeleteById
{

    /**
     * Delete of the User by ID
     *
     * @param int  $id
     * @param \PDO $db
     */
    public static function delete($id, \PDO $db)
    {
        $sql = 'DELETE FROM users WHERE id= ? LIMIT 1';
        $STH = $db->prepare($sql);
        $STH->execute([$id]);
    }

}
