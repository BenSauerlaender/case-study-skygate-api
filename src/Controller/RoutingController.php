<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller;

use Objects\ApiMethod;
use Objects\Interfaces\ApiPathInterface;
use Controller\Interfaces\RoutingControllerInterface;
use Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use Exceptions\RoutingExceptions\ApiPathNotFoundException;

/**
 * Implementation of RoutingControllerInterface
 */
class RoutingController implements RoutingControllerInterface
{

    /**
     * An array of all available routes
     *
     * @var array $routes = [
     *   $path (string) => [
     *     $method (string) => [ 
     *       "params"         => (array<string>)    List of route paramterss.
     *       "requireAuth"    => (bool)             True if authentication is required.
     *       "function"       => (Closure)          Anonymous function that process the route.
     *     ]
     *   ]
     * ]
     */
    private array $routes;

    /**
     * @param  array $routes = [
     *   $path (string) => [
     *     $method (string) => [ 
     *       "params"         => (array<string>)    List of route paramterss.
     *       "requireAuth"    => (bool)             True if authentication is required.
     *       "function"       => (Closure)          Anonymous function that process the route.
     *     ]
     *   ]
     * ]
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function route(ApiPathInterface $requestedPath, ApiMethod $requestedMethod): array
    {
        //get the requested path in the same format it is saved in the routes
        $pathWPlaceholder = $requestedPath->getStringWithPlaceholders();

        //throw exception if no route-path matches the requested-path
        if (!array_key_exists($pathWPlaceholder, $this->routes)) throw new ApiPathNotFoundException("No route matching the path: $requestedPath can be found");

        //get the path object (a list of available methods)
        $path = $this->routes[$pathWPlaceholder];

        //get the requested method as string
        $methodString = $requestedMethod->toString();

        //throw exception if requested method is not supported
        if (!array_key_exists($methodString, $path)) {
            throw new ApiMethodNotFoundException("The Path: $requestedPath has no method: $methodString;", array_keys($path));
        }

        //get the route
        $route = $path[$methodString];

        //get the provided parameters from the request
        $requestedParams = $requestedPath->getParameters();

        //save the parameters with there names as keys
        $paramsWithNames = [];
        foreach ($route["params"] as $idx => $key) {
            $paramsWithNames[$key] = $requestedParams[$idx];
        }

        return [
            "params"        => $paramsWithNames,
            "requireAuth"   => $route["requireAuth"],
            "function"      => $route["function"]
        ];
    }
}
