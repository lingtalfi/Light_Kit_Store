<?php


namespace Ling\Light_Kit_Store\Api\Custom\Interfaces;

use Ling\Light_Kit_Store\Api\Generated\Interfaces\UserApiInterface;


/**
 * The CustomUserApiInterface interface.
 */
interface CustomUserApiInterface extends UserApiInterface
{

    /**
     * Returns the user row identified by the given remember_me token.
     *
     *
     * If the row is not found, this method's return depends on the throwNotFoundEx flag:
     * - if true, the method throws an exception
     * - if false, the method returns the given default value
     *
     *
     * @param string $rememberMeToken
     * @param mixed $default = null
     * @param bool $throwNotFoundEx = false
     * @return mixed
     * @throws \Exception
     */
    public function getUserByRememberMeToken(string $rememberMeToken, mixed $default = null, bool $throwNotFoundEx = false);



}
