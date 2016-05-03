<?php

namespace Dore\Core\User\Tools;

use Dore\Core\User\Exception\InvalidInputException;
use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Exception\UserNotFoundException;

/**
 * Class FindByLogin
 * @package Dore\Core\User\Tools
 */
class FindByLogin
{

    /**
     * Search of the User by a Nickname or Email
     *
     * @param string $login
     * @param \PDO   $db
     * @return array
     * @throws IUserException
     */
    public static function find($login, \PDO $db)
    {
        try {
            self::checkInquiry($login);

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $sql = "SELECT * FROM `users` WHERE `email` = :login AND `provider` = 'native' LIMIT 1";
            } else {
                $sql = "SELECT * FROM `users` WHERE `nickname` = :login AND `provider` = 'native' LIMIT 1";
            }

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':login', $login, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            self::checkResult($user, $login);

            return $user;
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Check input data
     *
     * @param string $login
     * @throws InvalidInputException
     */
    private static function checkInquiry($login)
    {
        if (empty($login)) {
            throw new InvalidInputException(_s('The login is required'));
        }
    }

    /**
     * Check result
     *
     * @param mixed  $result
     * @param string $login
     * @throws UserNotFoundException
     */
    private static function checkResult($result, $login)
    {
        if (false === $result || !is_array($result)) {
            throw new UserNotFoundException(sprintf(_s('A user with login [%s] not found.'), $login));
        }
    }

}
