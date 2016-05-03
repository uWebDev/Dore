<?php

namespace Dore\Core\User\Tools;

use Dore\Core\User\Exception\InvalidInputException;
use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Exception\UserNotFoundException;

/**
 * Class FindBySocialIdAndProvider
 * @package Dore\Core\User\Tools
 */
class FindBySocialIdAndProvider
{

    /**
     * Search of the User by Social ID and Provider
     *
     * @param int    $id
     * @param string $provider
     * @param \PDO   $db
     *
     * @return array
     * @throws IUserException
     */
    public static function find($id, $provider, \PDO $db)
    {
        try {
            self::checkInquiryId($id);
            self::checkInquiryProvider($provider);

            $stmt = $db->prepare('SELECT * FROM `users` WHERE `socialId` = :id AND `provider` = :provider LIMIT 1');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':provider', $provider, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            self::checkResult($user, $id);

            return $user;
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Check input id
     *
     * @param int $id
     * @throws InvalidInputException
     */
    private static function checkInquiryId($id)
    {
        if (!is_numeric($id) || $id < 1) {
            throw new InvalidInputException(sprintf(_s('The argument [%d] must be a valid User ID'), $id));
        }
    }

    /**
     * Check input provider
     *
     * @param string $provider
     * @throws InvalidInputException
     */
    private static function checkInquiryProvider($provider)
    {
        if (empty($provider)) {
            throw new InvalidInputException(_s('The provider is required'));
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
