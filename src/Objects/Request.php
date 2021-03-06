<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects;

use Objects\ApiMethod;
use Objects\ApiPath;
use Objects\Interfaces\RequestInterface;
use Objects\Interfaces\ApiPathInterface;
use Exceptions\InvalidRequestExceptions\InvalidCookieException;
use Exceptions\InvalidRequestExceptions\InvalidQueryException;

/**
 * Class that implements the RequestInterface
 */
class Request implements RequestInterface
{
    /** The Path to the requested Resource */
    private ApiPathInterface $path;

    /** The Http Method provided by the request */
    private ApiMethod $method;

    /** The headers provided by the request @var array<string,string> */
    private array $headers;

    /** The cookies provided by the request @var array<string,string> */
    private array $cookies;

    /** The query provided by the request @var array<string,string|int> */
    private array $query;

    /** The Body provided by the request */
    private ?array $body;

    /**
     * Constructs a request
     *
     * @param  string $path                     The requested path as string without a prefix (like /api/v1)
     * @param  string $method                   The request method. E.g. GET
     * @param  string $query                    The query string from the request
     * @param  array<string,string>  $headers   The headers provided by the request.
     * 
     * @throws InvalidRequestException             if the input can not be parsed in a valid request
     *      (InvalidPathException | InvalidMethodException | InvalidQueryException | InvalidCookieException)
     */
    public function __construct(string $path, string $method, string $query = "", array $headers = [], ?array $body = null)
    {
        //validate/parse the path-string to apiPath
        $this->path = new ApiPath($path);

        //validate/parse the method-string to apiMethod
        $this->method = ApiMethod::fromString($method);

        //validate/parse the query-string to query-array
        $this->query = $this->parseQuery($query);

        //validate/parse the header-array
        $cookieAndHeader = $this->parseHeaders($headers);

        //save the cookies and headers as separated arrays
        $this->headers = $cookieAndHeader["headers"];
        $this->cookies = $cookieAndHeader["cookies"];

        //save the body
        $this->body = $body;
    }

    /**
     * Parses a query string in a query array
     *
     * @param  string $query            The Raw query string.
     * 
     * @throws InvalidQueryException    if the query string can not be parsed into an valid array.
     */
    private function parseQuery(string $query): array
    {
        $ret = [];

        //remove unwanted spaces
        $query = str_replace(" ", "", $query);

        //if not empty
        if ($query != "") {
            //for each query pair
            foreach (explode("&", $query) as $p) {
                //separate parameter name from value
                $pair = explode("=", $p);

                //lowercase the parameter name
                $pair[0] = strtolower($pair[0]);

                //allow only letters for the parameter name
                if (preg_match("/^[a-z]+$/", $pair[0]) !== 1) throw new InvalidQueryException("'$p' contains invalid characters");

                //no value: set parameter-name also as value
                if (sizeof($pair) === 1) {
                    $pair[1] = $pair[0];
                }

                //pair has exact one key and one value
                if (sizeof($pair) !== 2) throw new InvalidQueryException("'$p' is not a valid");

                //if value is an int: save as int otherwise get the string
                if (ctype_digit($pair[1])) {
                    $val = (int) $pair[1];
                } else {
                    //fix url encoding
                    $val = str_replace("+", " ", $pair[1]);
                }
                //save the query pair
                $ret[$pair[0]] = $val;
            }
        }
        return $ret;
    }

    /**
     * Validates and parses a headers array in to separated headers- and cookies- arrays
     *
     * @param  array<string,string> $headers    The Raw query string.
     * 
     * @throws InvalidCookieException           if a cookie can not be parsed into an valid array.
     */
    private function parseHeaders(array $headers): array
    {
        $retHeaders = [];
        $retCookies = [];

        //for each header
        foreach ($headers as $key => $value) {

            //key to lowercase
            $key = strtolower($key);

            //if key is cookie save in cookies not in headers
            if ($key == "cookie") {
                foreach (explode("; ", $value) as $cookie) {
                    //separate key and value
                    $pair = explode("=", $cookie);

                    if (sizeof($pair) !== 2) throw new InvalidCookieException($cookie);

                    //save the cookie
                    $retCookies[strtolower($pair[0])] = $pair[1];
                }
            } else {
                //save the header
                $retHeaders[$key] = $value;
            }
        }
        return ["cookies" => $retCookies, "headers" => $retHeaders];
    }

    public function getQueryValue(string $parameter): mixed
    {
        //get the parameter in lowercase, so its not case sensitive
        $parameter = strtolower($parameter);

        //return the value or null if its not there
        if (array_key_exists($parameter, $this->query)) {
            return $this->query[$parameter];
        } else {
            return null;
        }
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getHeader(string $key): ?string
    {
        //get the key in lowercase, so its not case sensitive
        $key = strtolower($key);

        //return the value or null if its not there
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        } else {
            return null;
        };
    }

    public function getCookie(string $key): ?string
    {
        //get the key in lowercase, so its not case sensitive
        $key = strtolower($key);

        //return the value or null if its not there
        if (array_key_exists($key, $this->cookies)) {
            return $this->cookies[$key];
        } else {
            return null;
        };
    }

    public function getAccessToken(): ?string
    {
        //get authorization header 
        $token = $this->getHeader("Authorization");

        //return the token if it has the correct format: 'Bearer <token>'
        if (is_string($token)) {
            $exploded = explode(" ", $token);
            if (sizeof($exploded) === 2) {
                if ($exploded[0] === "Bearer") {
                    return $exploded[1];
                }
            }
        }

        //return null if there is no bearer token in the correct format
        return null;
    }

    public function getPath(): ApiPathInterface
    {
        return $this->path;
    }

    public function getMethod(): ApiMethod
    {
        return $this->method;
    }

    public function getBody(): ?array
    {
        return $this->body;
    }
}
