<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use Exceptions\ValidationExceptions\InvalidPropertyException;
use Exceptions\ValidationExceptions\MissingPropertiesException;
use Exceptions\ValidationExceptions\ValidationException;

/**
 * Integration Tests for the createUser method of UserController
 */
final class UCICreateTest extends BaseUCITest
{
    /**
     * Tests if the user creation throws Validation Exception by an invalid property-array
     * 
     * @dataProvider invalidPropertyArrayProvider
     */
    public function testCreateUserFailsOnInvalidPropertyData(array $properties, string $exception): void
    {
        $this->expectException(ValidationException::class);
        $this->expectException($exception);

        $this->userController->createUser($properties);
    }

    /**
     * Tests if the user creation succeeds
     * 
     * @dataProvider propertyArrayProvider
     */
    public function testCreateUser(array $properties): void
    {
        $response = $this->userController->createUser($properties);
        $this->assertArrayHasKey("id", $response);
        $this->assertIsInt($response["id"]);
        $this->assertEquals(1, $response["id"]);

        $this->assertArrayHasKey("verificationCode", $response);
        $this->assertIsString($response["verificationCode"]);
        $this->assertEquals(10, strlen($response["verificationCode"]));
    }

    /**
     * Tests if the creation fails if the email is already taken
     */
    public function testCreateUserFailsOnSameEmail(): void
    {
        $this->createUser();

        $this->expectException(InvalidPropertyException::class);

        $this->userController->createUser(
            [
                "email"     => "myEmail@mail.de",
                "name"      => "myName",
                "postcode"  => "12345",
                "city"      => "myCity",
                "phone"     => "123456789",
                "password"  => "MyPassword1"
            ]
        );
    }

    public function propertyArrayProvider(): array
    {
        return [
            "with role" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                    "role"  => "user"
                ]
            ],
            "without role" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                ]
            ]
        ];
    }

    public function invalidPropertyArrayProvider(): array
    {
        return [
            "missing email and name" => [
                [
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], MissingPropertiesException::class
            ],
            "unsupported property" => [
                [
                    "quatsch"     => "",
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], InvalidPropertyException::class
            ],
            "invalid type" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => 123,
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                    "role"      => "myRole"
                ], InvalidPropertyException::class
            ],
            "invalid email and password" => [
                [
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "mypassword",
                ], InvalidPropertyException::class
            ]
        ];
    }
}
