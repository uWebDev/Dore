<?php

namespace Dore\Core\User\User;

/**
 * Class EmptyUser
 * @package Dore\Core\User\User
 */
class EmptyUser extends AbstractUser
{

    public function __construct()
    {
        $this->setFlags(parent::ARRAY_AS_PROPS);
    }

    public function offsetGet($key, $ignore = false)
    {
        $values = [
            'id'      => 0,
            'rights'  => 0,
            'nickname'    => null,
            'config' => '',
            'activeted'  => 1,
            'ban'     => 0,
        ];

        return isset($values[$key]) ? $values[$key] : false;
    }

    public function checkPassword($password = null)
    {
        return false;
    }

    public function checkToken($token = null)
    {
        return false;
    }

    public function save()
    {
        return false;
    }

}
