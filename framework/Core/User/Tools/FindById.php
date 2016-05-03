<?php

namespace Dore\Core\User\Tools;

use Dore\Core\User\Exception\InvalidInputException;
use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Exception\UserNotFoundException;

/**
 * Class FindById
 * @package Dore\Core\User\Tools
 */
class FindById
{

    /**
     * Search of the User by ID
     *
     * @param int  $id
     * @param \PDO $db
     *
     * @return array
     * @throws IUserException
     */
    public static function find($id, \PDO $db)
    {
        try {
            self::checkInquiry($id);

            $stmt = $db->prepare('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();

            self::checkResult($user, $id);

            return $user;
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Check input data
     *
     * @param int $id
     *
     * @throws InvalidInputException
     */
    private static function checkInquiry($id)
    {
        if (!is_numeric($id) || $id < 1) {
            throw new InvalidInputException(sprintf(_s('The argument [%d] must be a valid User ID'), $id));
        }
    }

    /**
     * Check result
     *
     * @param mixed $result
     * @param int   $id
     *
     * @throws UserNotFoundException
     */
    private static function checkResult($result, $id)
    {
        if (false === $result || !is_array($result)) {
            throw new UserNotFoundException(sprintf(_s('A user with ID [%d] not found.'), $id));
        }
    }

}
