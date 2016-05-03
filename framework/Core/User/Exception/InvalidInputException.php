<?php

namespace Dore\Core\User\Exception;

use Dore\Core\Exception\SemanticExceptions\InvalidArgumentException;

/**
 * Class InvalidInputException Неверный ввод
 * @package Dore\Core\User\Exception
 */
class InvalidInputException extends InvalidArgumentException implements IUserException
{

}
