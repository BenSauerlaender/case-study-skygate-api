<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces;

/**
 * ECR = email change request = request to change the email address 
 */

// functions to interact with the emailChangeRequest data resource
interface EcrAccessorInterface
{

    //find Request by userID and return id, newEmail and verificationCode
    public function findByUserID(int $user_id): ?array;

    //find Request for specified email and return id or null if there is no user with this email
    public function findByEmail(string $email): ?int;

    //delete request by ID
    public function delete(int $id): void;

    //delete request by userID
    public function deleteByUserID(int $user_id): void;

    //insert a new request
    public function insert(int $user_id, string $new_email, string $verification_code): void;
}