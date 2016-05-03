<?php

namespace Dore\Core\User\Exception;

use Dore\Core\Exception\DataExceptions\NotFoundDataException;

/**
 * Class UserNotFoundException
 * @package Dore\Core\User\Exception
 */
class UserNotFoundException extends NotFoundDataException implements IUserException
{

}
