<?php


namespace Ling\Light_Kit_Store\Api\Custom\Classes;

use Ling\Light_Kit_Store\Api\Generated\Classes\UserApi;
use Ling\Light_Kit_Store\Api\Custom\Interfaces\CustomUserApiInterface;



/**
 * The CustomUserApi class.
 */
class CustomUserApi extends UserApi implements CustomUserApiInterface
{


    /**
     * Builds the CustomUserApi instance.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @implementation
     */
    public function getUserByRememberMeToken(string $rememberMeToken, mixed $default = null, bool $throwNotFoundEx = false)
    {
        $ret = $this->pdoWrapper->fetch("select * from `$this->table` where remember_me_token=:token", [
            "token" => $rememberMeToken,

        ]);
        if (false === $ret) {
            if (true === $throwNotFoundEx) {
                throw new \RuntimeException("Row not found with remember_me_token=$rememberMeToken.");
            } else {
                $ret = $default;
            }
        }
        return $ret;
    }


}
