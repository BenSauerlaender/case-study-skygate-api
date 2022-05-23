<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions;

use Throwable;

/**
 * Exception that is thrown if trying to add a duplicate UserID to a unique field in the database.
 */
class DuplicateUserException extends UniqueFieldException
{
    public function __construct(int $userID, Throwable $previous = null)
    {
        parent::__construct("userID=$userID is already taken.", 0, $previous);
    }
}
