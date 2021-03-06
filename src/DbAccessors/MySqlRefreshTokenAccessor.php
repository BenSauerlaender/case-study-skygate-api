<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace DbAccessors;

use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use Exceptions\DBExceptions\FieldNotFoundExceptions\FieldNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Implementation of refreshTokenAccessorInterface
 */
class MySqlRefreshTokenAccessor extends MySqlAccessor implements RefreshTokenAccessorInterface
{
    public function getCountByUserID(int $userID): ?int
    {
        $sql = 'SELECT count
                FROM refreshToken
                WHERE user_id=:userID;';

        $stmt = $this->prepareAndExecute($sql, ["userID" => $userID]);

        $response =  $stmt->fetchAll();

        //if no Request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["count"];
    }

    public function deleteByUserID(int $userID): void
    {
        $sql = 'DELETE 
                FROM refreshToken
                WHERE user_id=:userID;';

        $this->prepareAndExecute($sql, ["userID" => $userID]);
    }

    public function increaseCount(int $userID): void
    {
        //if no entry exist create one
        //else increase by one
        $sql = 'INSERT INTO refreshToken 
                    (user_id) 
                VALUES 
                    (:userID) 
                ON DUPLICATE KEY UPDATE 
                    count = count + 1;';

        try {
            $this->prepareAndExecute($sql, ["userID" => $userID]);
        } catch (FieldNotFoundException $e) {
            throw new UserNotFoundException($userID, null, $e);
        }
    }
}
