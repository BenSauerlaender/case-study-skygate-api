<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\InvalidResponseExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidResponseExceptions\InvalidResponseException;

/**
 * Exception that should be thrown if try to set an unsupported response header
 */
class UnsupportedResponseHeaderException extends InvalidResponseException
{
}