<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\ValidationExceptions\InvalidPropertyException;

/**
 * Test suite for UserController->requestUsersEmailChange method
 */
final class UCRequestEmailChangeTest extends BaseUCTest
{

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testRequestEmailUserNotExists(): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(1, "email", "code"))
            ->will($this->throwException(new UserNotFoundException(1)));


        $this->expectException(UserNotFoundException::class);
        $this->userController->requestUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the Email is invalid
     */
    public function testRequestEmailWithInvalidEmail(): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->willReturn(["email" => ["TO_SHORT"]]);

        $this->expectException(InvalidPropertyException::class);

        $this->userController->requestUsersEmailChange(1, "email");
    }


    /**
     * Tests if the method throws an exception if the Email is not free
     * 
     * @dataProvider \tests\Unit\Controller\UserController\Provider::NANDProvider()
     */
    public function testRequestEmailWithNotFreeEmail($emailFreeInUser, $emailFreeInEcr): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "someEmail"]))
            ->willReturn(true);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);


        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage("Invalid properties with Reasons: email: IS_TAKEN");

        $this->userController->requestUsersEmailChange(1, "someEmail");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testRequestEmailSuccessful(): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "email"]))
            ->willReturn(true);

        $this->configEmailAvailability(true, true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("deleteByUserID")
            ->with($this->equalTo(1))
            ->will($this->throwException(new EcrNotFoundException(1, "userID")));

        $this->SecurityControllerMock->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("code");

        $this->ecrAccessorMock->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(1, "email", "code"));


        $code = $this->userController->requestUsersEmailChange(1, "email");
        $this->assertEquals("code", $code);
    }
}
