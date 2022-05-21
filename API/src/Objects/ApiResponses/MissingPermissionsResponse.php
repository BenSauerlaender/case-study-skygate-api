<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses;

/**
 * Response that should be used if the resource need permissions the requester don't have.
 */
class MissingPermissionsResponse extends BaseResponse
{
    public function __construct(array $requiredPermissions)
    {
        $this->setCode(403);
        $this->setData(["requiredPermissions" => $requiredPermissions]);
        $this->addMessage("The Route requires permissions you don't have.");
    }
}