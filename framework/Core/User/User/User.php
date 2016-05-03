<?php

namespace Dore\Core\User\User;

/**
 * Class User
 * @package Dore\Core\User\User
 */
class User extends AbstractUser
{

    /**
     * Construct a new User object
     *
     * @param array $user
     * @param \PDO  $db
     */
    public function __construct(array $user, \PDO $db)
    {
        $this->db = $db;
        parent::__construct($user, \ArrayObject::ARRAY_AS_PROPS);
    }

}
