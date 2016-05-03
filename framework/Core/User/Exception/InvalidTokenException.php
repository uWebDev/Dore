<?php

namespace Dore\Core\User\Exception;

use Dore\Core\Exception\SemanticExceptions\InvalidArgumentException;

/**
 * Class InvalidTokenException
 * @package Dore\Core\User\Exception
 */
class InvalidTokenException extends InvalidArgumentException implements IUserException
{

}
