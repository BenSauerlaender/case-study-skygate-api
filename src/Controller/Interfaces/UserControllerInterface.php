<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller\Interfaces;

/**
 * Controller for all User related stuff
 */
interface UserControllerInterface
{
    /**
     * Creates a new user
     * 
     * Validates all properties.
     * Hashes the password.
     * Generates verification code.
     * Writes the new user to the database.
     *
     * @param  array<string,string> $properties   The properties to give the new user
     *  $properties = [
     *      "email"     => (string)   The users e-mail. Required.
     *      "name"      => (string)   The users first and last name. Required.
     *      "postcode"  => (string)   The users postcode. Required.
     *      "city"      => (string)   The users city. Required.
     *      "phone"     => (string)   The users phone number. Required.
     *      "password"  => (string)   The users password. Required.
     *      "role"      => (string)   The users role. Default: "user"
     *  ]
     * @return array[id => (int), verificationCode => (string)] The user's id and the verification code to verify the user 
     * 
     * @throws DBexception                  if there is a problem with the database.
     * @throws ValidationException          if there are missing or invalid properties.
     *          (MissingPropertiesException | InvalidPropertyException)
     * @throws PasswordHashException        if there is a problem with the hashing the password.
     */
    public function createUser(array $properties): array;

    /**
     * Gets a user
     *
     * @param int $id   The users id.
     * @return  array<string,string> $properties   The User properties
     *  $properties = [
     *      "email"     => (string)   The users e-mail.
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "role"      => (string)   The users role.
     *  ]
     * 
     * @throws DBexception  if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function getUser(int $id): array;

    /**
     * Deletes a user
     *
     * @param  int  $id the user's id 
     * 
     * @throws DBexception    if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function deleteUser(int $id): void;

    /**
     * Updates the user's properties
     *
     * @param  int   $id the users id
     * @param  array<string,string> $properties   The properties to update.
     *  $properties = [
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "role"      => (string)   The users role. Options: "user", "admin".
     *  ]
     * 
     * @throws DBexception                  if there is a problem with the database.
     *          (UserNotFoundException | RoleNotFoundException ...)
     * @throws ValidationException          if properties array is invalid.
     *          (ArrayIsEmptyException | InvalidPropertyException )
     */
    public function updateUser(int $id, array $properties): void;

    /**
     * Verifies the user if the code is correct
     * 
     * Checks if the verificationCode is correct.
     * Updates the database accordingly
     *
     * @param  int    $id                   The users id.
     * @param  string $verificationCode     The code to verify the user
     * @return bool                         returns false if code is incorrect, true otherwise.
     * 
     * @throws BadMethodCallException   if the user is already verified.
     * @throws DBexception              if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function verifyUser(int $id, string $verificationCode): bool;

    /**
     * Checks if the user with the specified email has also the specified password
     *
     * @param  string $email    The users email.
     * @param  string $password The users password.
     * @return bool             returns true if the combination is correct, false otherwise
     * 
     * @throws DBexception        if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function checkEmailPassword(string $email, string $password): bool;

    /**
     * Changes the users password if the old one is correct
     * 
     * Checks if the old password matches.
     * Validates the new password.
     * Hashes the new password.
     * Write the new password to the database.
     *
     * @param  int    $id               The users id.
     * @param  string $new_password     The users old password.
     * @param  string $old_password     The users new password.
     * @return bool                     returns false if old password is incorrect, true otherwise.
     * 
     * @throws InvalidPropertyException     if the new password is not valid.
     * @throws DBexception                  if there is a problem with the database.
     *          (UserNotFoundException | ...)
     * @throws PasswordHashException        if there is a problem with the hashing the password.
     */
    public function updateUsersPassword(int $id, string $new_password, string $old_password): bool;

    /**
     * Changes the users password 
     * 
     * Validates the new password.
     * Hashes the new password.
     * Write the new password to the database.
     *
     * @param  int    $id               The users id.
     * @param  string $new_password     The users old password.
     * 
     * @throws InvalidPropertyException     if the new password is not valid.
     * @throws DBexception                  if there is a problem with the database.
     *          (UserNotFoundException | ...)
     * @throws PasswordHashException        if there is a problem with the hashing the password.
     */
    public function updateUsersPasswordPrivileged(int $id, string $new_password): void;

    /**
     * Creates an Request to change the users email.
     * 
     * Validates new email.
     * Generates a verification code.
     * Write a Request to change the users email to the database.
     *
     * @param  int    $id       The users id.
     * @param  string $newEmail The users new email.
     * @return string           The verification code to verify the Request.   
     *  
     * @throws InvalidPropertyException     if the new email is not valid.
     * @throws DBexception                  if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function requestUsersEmailChange(int $id, string $newEmail): string;

    /**
     * Verifies the Request to change the user's email if the code is correct
     * 
     * Gets the Request.
     * Checks if the verification code is correct.
     * Writes the new email to the database.
     * 
     * @param  int    $id       The users id.
     * @param  string $code     The verification code to verify the email change.
     * @return bool             returns false if code is incorrect, true otherwise.
     * 
     * @throws DBexception        if there is a problem with the database.
     *          (EcrNotFoundException| ...)
     */
    public function verifyUsersEmailChange(int $id, string $code): bool;
}
