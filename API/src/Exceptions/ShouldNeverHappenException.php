<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;

/**
 * Exception that is thrown if something happen that should not be possible
 */
class ShouldNeverHappenException extends BaseException
{
}
