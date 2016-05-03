<?php

namespace Dore\Core\Exception\SemanticExceptions;

use Dore\Core\Exception\SemanticException;

/**
 * Class PageForbiddenException
 * This is an exception to throw when the server understood the request,
 * but refuses to fulfill it due to restrictions in access to the client to the specified resource
 * @package Dore\Core\Exception\SemanticExceptions
 */
class PageForbiddenException extends SemanticException
{
    
}
