<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller\UserController;

/**
 * A Collection of DataProviders for UserController tests
 */
final class Provider
{
    /**
     * Provide all logic-NAND combinations of 2 bool's
     */
    public static function NANDProvider(): array
    {
        return [
            [false, false],
            [true, false],
            [false, true],
        ];
    }
}
