<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Cookies;

use Objects\Cookies\Interfaces\CookieInterface;

/**
 * Base class for all Cookies.
 */
abstract class BaseCookie implements CookieInterface
{
    private string $name;
    private string $value;

    /** Time in seconds */
    private int $expiresIn;

    private bool $secure;
    private bool $httpOnly;


    public function __construct(string $name, string $value, int $expiresIn, string $path, bool $secure, bool $httpOnly)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expiresIn = $expiresIn;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function get(): array
    {
        return [
            "name"      => $this->name,
            "value"     => $this->value,
            "expiresIn" => $this->expiresIn,
            "path"      => $this->path,
            "secure"    => $this->secure,
            "httpOnly"  => $this->httpOnly
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
