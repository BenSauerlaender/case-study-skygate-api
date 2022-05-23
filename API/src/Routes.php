<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserQueryInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Objects\Cookies\RefreshTokenCookie;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\InvalidPropertyResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\InvalidQueryResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\MissingPropertyResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\UserNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\RedirectionResponses\RedirectionResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses\CreatedResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses\DataResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses\NoContentResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses\SetCookieResponse;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;
use BenSauer\CaseStudySkygateApi\Utilities\MailSender;
use InvalidArgumentException;

class Routes
{
    public static function getRoutes(): array
    {
        return [
            "/register" => [
                "POST" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        $fields = $req->getBody();
                        $fields["role"] = "user";

                        try {
                            $ret = $uc->createUser($fields);

                            MailSender::sendVerificationRequest($fields["email"], $fields["name"], $ret["id"], $ret["verificationCode"]);

                            return new CreatedResponse();
                        } catch (RequiredFieldException $e) {
                            return new MissingPropertyResponse($e->getMissing());
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidField());
                        }
                    }
                ]
            ],
            "/users/{id}/verify/{id}" => [
                "GET" => [
                    "ids" => ["userID", "verificationCode"],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->verifyUser($ids["userID"], "{$ids["verificationCode"]}")) {
                                return new RedirectionResponse("{$_ENV['API_PROD_DOMAIN']}/login");
                            } else {
                                return new BadRequestResponse("The verification code is invalid.", 211);
                            }
                        } catch (BadMethodCallException $e) {
                            return new BadRequestResponse("The user is already verified.", 210);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/login" => [
                "POST" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {

                        $fields = $req->getBody();

                        $missingFields = array_diff_key(["email" => null, "password" => null], $fields ?? []);

                        $email = strtolower($fields["email"] ?? "");
                        $pass = $fields["password"] ?? "";

                        if (sizeOf($missingFields) !== 0) {
                            return new MissingPropertyResponse(array_keys($missingFields));
                        }

                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->checkEmailPassword($email, $pass)) {
                                /** @var AuthenticationControllerInterface */
                                $auth = $this->controller["auth"];
                                $token = $auth->getNewRefreshToken($email);
                                return new SetCookieResponse(new RefreshTokenCookie($token));
                            } else {
                                return new BadRequestResponse("The password is incorrect", 215);
                            }
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/token" => [
                "GET" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {

                        $refreshJWT = $req->getCookie("skygatecasestudy.refreshtoken");
                        if (is_null($refreshJWT)) {
                            return new BadRequestResponse("No refreshToken provided! POST /login to get one.", 301);
                        }

                        /** @var AuthenticationControllerInterface */
                        $auth = $this->controller["auth"];

                        try {
                            $accessToken = $auth->getNewAccessToken($refreshJWT);
                            return new DataResponse(["accessToken" => $accessToken]);
                        } catch (InvalidArgumentException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "NOT_VERIFIABLE"]);
                        } catch (ExpiredTokenException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "EXPIRED"]);
                        } catch (InvalidTokenException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "OLD_TOKEN"]);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/users/{id}" => [
                "GET" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $user = $uc->getUser($ids["userID"]);
                            return new DataResponse($user);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ],
                "PUT" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {
                        $availableFields = ["name" => null, "postcode" => null, "city" => null, "phone" => null, "role" => null];

                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        $fields = array_intersect_key($req->getBody() ?? [], $availableFields);

                        if (sizeOf($fields) === 0) return new BadRequestResponse("No available properties provided.", 101, ["availableProperties" => array_keys($availableFields)]);

                        try {
                            $uc->updateUser($ids["userID"], $fields);
                            return new DataResponse(["updated" => $fields]);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        } catch (RoleNotFoundException $e) {
                            return new InvalidPropertyResponse(["role" => ["INVALID"]]);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidField());
                        }
                    }
                ],
                "DELETE" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:delete:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $uc->deleteUser($ids["userID"]);
                            return new NoContentResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/users/{id}/password" => [
                "PUT" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {

                        $fields = $req->getBody();

                        $missingFields = array_diff_key(["oldPassword" => null, "newPassword" => null], $fields ?? []);

                        if (sizeOf($missingFields) !== 0) {
                            return new MissingPropertyResponse(array_keys($missingFields));
                        }
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->updateUsersPassword($ids["userID"], $fields["newPassword"], $fields["oldPassword"])) {
                                /** @var RefreshTokenAccessorInterface*/
                                $acc = $this->accessors["refreshToken"];
                                $acc->increaseCount($ids["userID"]);
                                return new NoContentResponse();
                            } else {
                                return new BadRequestResponse("The password is incorrect", 215);
                            }
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidField());
                        }
                    }
                ]
            ],
            "/users/{id}/emailchange" => [
                "POST" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {

                        $fields = $req->getBody();

                        $missingFields = array_diff_key(["email" => null], $fields ?? []);

                        if (sizeOf($missingFields) !== 0) {
                            return new MissingPropertyResponse($missingFields);
                        }
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $code = $uc->requestUsersEmailChange($ids["userID"], $fields["email"]);

                            $user = $uc->getUser($ids["userID"]);

                            MailSender::sendEmailChangeVerificationRequest($fields["email"], $user["name"], $ids["userID"], $code);
                            return new CreatedResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidField());
                        }
                    }
                ]
            ],
            "/users/{id}/logout" => [
                "POST" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:delete:{userID}"],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var RefreshTokenAccessorInterface*/
                        $acc = $this->accessors["refreshToken"];
                        try {
                            $acc->increaseCount($ids["userID"]);
                            return new NoContentResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/users/{id}/emailchange/{id}" => [
                "GET" => [
                    "ids" => ["userID", "verificationCode"],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->verifyUsersEmailChange($ids["userID"], "{$ids["verificationCode"]}")) {
                                return new RedirectionResponse("{$_ENV['API_PROD_DOMAIN']}/email-changed");
                            } else {
                                return new BadRequestResponse("The verification code is invalid.", 211);
                            }
                        } catch (EcrNotFoundException $e) {
                            return new BadRequestResponse("The user has no open email change request.", 212);
                        }
                    }
                ]
            ],
            "/users" => [
                "GET" => [
                    "ids" => [],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{all}"],
                    "function" => function (RequestInterface $req, array $ids) {

                        $queryConfig = $req->getQuery();

                        /** @var UserQueryInterface */
                        $uq = $this->accessors["userQuery"];

                        try {
                            $uq->configureByArray($queryConfig, ["page", "index"]);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidQueryResponse();
                        }

                        $pagesize = $queryConfig["page"] ?? null;
                        if (!is_null($pagesize)) {
                            $index = $queryConfig["index"] ?? 0;
                            $ret = $uq->getResultsPaginated($pagesize, $index);
                        } else {
                            $ret = $uq->getResults();
                        }

                        return new DataResponse($ret);
                    }
                ]
            ],
            "/users/length" => [
                "GET" => [
                    "ids" => [],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{all}"],
                    "function" => function (RequestInterface $req, array $ids) {
                        $queryConfig = $req->getQuery();

                        /** @var UserQueryInterface */
                        $uq = $this->accessors["userQuery"];

                        try {
                            $uq->configureByArray($queryConfig, ["page", "index"]);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidQueryResponse();
                        }
                        return new DataResponse(["length" => $uq->getLength()]);
                    }
                ]
            ],
            "/roles" => [
                "GET" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $ids) {
                        /** @var RoleAccessorInterface*/
                        $acc = $this->accessors["role"];

                        return new DataResponse($acc->getList());
                    }
                ]
            ],
        ];
    }
}
