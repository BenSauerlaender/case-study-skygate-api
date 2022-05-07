<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;

/**
 * Controller that handles all the routes and finds the right.
 */
interface RoutingControllerInterface
{
    /**
     * Searches for a route that matches path and method and returns the route in convenient array
     *
     * @param  ApiPathInterface     $path       Requested Path.
     * @param  ApiMethod            $method     Requested Method.
     * @return array<string,mixed>  $route      The found route.
     *   $route = [
     *     "ids"            => (array<string,mixed>)    Key-Value pair for each path replacement.
     *     "requireAuth"    => (bool)                   True if authentication is required.
     *     "permissions"    => (array<string>)          List of required permissions.
     *     "function"       => (Closure)                Anonymous function that process the route.
     *   ]
     * @throws ApiPathNotFoundException     if there is no route with this path.
     * @throws ApiMethodNotFoundException   if there is no route with this path, that uses this method.
     */
    public function route(ApiPathInterface $path, ApiMethod $method): array;
}