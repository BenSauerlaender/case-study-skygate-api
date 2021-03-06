<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Objects;

use Exceptions\InvalidRequestExceptions\InvalidPathException;
use Objects\ApiPath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ApiPath class
 */
final class ApiPathTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid path
     * 
     * @dataProvider invalidApiPathProvider
     */
    public function testApiPathConstructionFailsByInvalidPath($input): void
    {
        $this->expectException(InvalidPathException::class);
        new ApiPath($input);
    }

    public function invalidApiPathProvider(): array
    {
        return [
            "empty string" => [""],
            "empty sub-part" => ["test//test"],
            "invalid character" => ["/test+ding/jo"],
            "combination of letter and number" => ["/test/t1"]
        ];
    }

    /**
     * Tests if the method returns the correct array
     * 
     * @dataProvider ApiPathProvider
     */
    public function testApiPathSuccessful(string $in, array $exp): void
    {
        $return = (new ApiPath($in))->getArray();
        $this->assertEquals($return, $exp);
    }

    public function ApiPathProvider(): array
    {
        return [
            ["test", ["test"]],
            ["TEST", ["test"]],
            ["/test", ["test"]],
            ["test/", ["test"]],
            ["/1/2/0", [1, 2, 0]],
            ["/test/123/test", ["test", 123, "test"]],
            ["/tEst/123/teST", ["test", 123, "test"]]
        ];
    }

    /**
     * Tests if the getParameters method returns the correct array
     * 
     * @dataProvider ApiPathWithParamsProvider
     */
    public function testApiPathGetParameters(string $in, array $exp): void
    {
        $return = (new ApiPath($in))->getParameters();
        $this->assertEquals($return, $exp);
    }

    public function ApiPathWithParamsProvider(): array
    {
        return [
            ["1", [1]],
            ["/test", []],
            ["test/1", [1]],
            ["/1/2/test/3", [1, 2, 3]]
        ];
    }

    /**
     * Tests if the getLength method returns the correct length
     * 
     * @dataProvider ApiPathLengthProvider
     */
    public function testApiPathGetLength(string $in, int $exp): void
    {
        $return = (new ApiPath($in))->getLength();
        $this->assertEquals($return, $exp);
    }

    public function ApiPathLengthProvider(): array
    {
        return [
            ["/test/", 1],
            ["/test/test", 2],
            ["test/1", 2],
            ["/1/2/test/3", 4]
        ];
    }

    /**
     * Tests if the getStringWithPlaceholders method returns the correct string
     * 
     * @dataProvider ApiPathStringWithPlaceholderProvider
     */
    public function testApiPathGetStringWithPlaceholder(string $in, string $exp): void
    {
        $return = (new ApiPath($in))->getStringWithPlaceholders();
        $this->assertEquals($return, $exp);
    }

    public function ApiPathStringWithPlaceholderProvider(): array
    {
        return [
            ["/test/", "/test"],
            ["/test/test", "/test/test"],
            ["test/1", "/test/{x}"],
            ["/1/2/test/3", "/{x}/{x}/test/{x}"]
        ];
    }
}
