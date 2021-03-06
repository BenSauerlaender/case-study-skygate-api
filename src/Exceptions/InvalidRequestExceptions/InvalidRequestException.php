<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\InvalidRequestExceptions;

use Exceptions\BaseException;

/**
 * Exception, that should be thrown if an Api Request is unexpected and ant be parsed
 */
class InvalidRequestException extends BaseException
{
}
