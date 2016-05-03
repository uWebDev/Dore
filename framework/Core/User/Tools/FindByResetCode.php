<?php

namespace Dore\Core\User\Tools;

use Dore\Core\User\Exception\InvalidInputException;
use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Exception\UserNotFoundException;

/**
 * Class FindByResetCode
 * @package Dore\Core\User\Tools
 */
class FindByResetCode
{

    /**
     * Search of the User by Activation Code
     *
     * @param string $code
     * @param \PDO   $db
     * @return array
     * @throws IUserException
     */
    public static function find($code, \PDO $db)
    {
        try {
            self::checkInquiry($code);

            $stmt = $db->prepare("SELECT * FROM `users` WHERE `passwordResetHash` = :code AND `provider` = 'native' LIMIT 1");
            $stmt->bindParam(':code', $code, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            self::checkResult($user, $code);

            return $user;
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Check input data
     *
     * @param string $code
     * @throws InvalidInputException
     */
    private static function checkInquiry($code)
    {
        if (empty($code)) {
            throw new InvalidInputException(_s('The code is required'));
        }
    }

    /**
     * Check result
     *
     * @param mixed  $result
     * @param string $code
     * @throws UserNotFoundException
     */
    private static function checkResult($result, $code)
    {
        if (false === $result || !is_array($result)) {
            throw new UserNotFoundException(_s('The link for changing the password is not valid'));
        }
    }

}
