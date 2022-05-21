<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\RedirectionResponse;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

/**
 * Response to use if the requester should be redirected to another url after a successful request.
 */
final class RedirectionResponse extends BaseResponse
{
    public function __construct(string $url)
    {
        $this->setCode(303);
        $this->addHeader("Location", $url);
    }
}