<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidRequestException;

/**
 * Exception, that should be thrown if an query string is not valid
 */
class InvalidQueryException extends InvalidRequestException
{
}
