<?php

namespace Dore\Core\User\User;

/**
 * Class Ban
 * @package Dore\Core\User\User
 */
class Ban
{

    /**
     * @var AbstractUser
     */
    protected $userInstance;

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var array
     */
    protected $ban;

    /**
     * @param AbstractUser $user
     * @param \PDO $db
     */
    public function __construct(AbstractUser $user, \PDO $db)
    {
        $this->userInstance = $user;
        $this->db           = $db;
        $this->ban          = [];
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->ban);
    }

    /**
     * Block user
     */
    public function set()
    {
        
    }

    public function save()
    {
//        if (count($this->changed)) {
//            $sql  = [];
//            $data = [];
//
//            foreach ($this->changed as $key => $val) {
//                $sql[]  = '`' . $key . '` = ?';
//                $data[] = $this->offsetGet($key, true);
//            }
//
//            $stmt = $this->db->prepare("UPDATE `users_ban` SET " . implode(',', $sql)
//                    . " WHERE `user_id` = {$this->offsetGet('user_id')} LIMIT 1");
//            $stmt->execute($data);
//        }
    }

    /**
     * @return array
     */
    public function check()
    {
        if ($this->userInstance->offsetGet('ban') == 1) {
            $sql = 'SELECT * FROM `users_ban` WHERE `id` = ? AND `banTime`  > UNIX_TIMESTAMP()';
            $STH = $this->db->prepare($sql);
            $STH->execute([$this->userInstance->offsetGet('id')]);
            if ($STH->rowCount()) {
                $i      = 0;
                while ($result = $STH->fetchAll()) {
                    $this->ban[$result[$i]['banType']] = 'ok';
                    ++$i;
                }
            } else {
                // если бана нет то в таблице user убрать метку о наличии бана
                $this->changеLabel(0);
            }
        }

        return $this;
    }

    /**
     * Change the label to ban a user
     *
     * @param $label
     */
    protected function changеLabel($label)
    {
        $this->userInstance->offsetSet('ban', $label);
        $this->userInstance->save();
    }

}
