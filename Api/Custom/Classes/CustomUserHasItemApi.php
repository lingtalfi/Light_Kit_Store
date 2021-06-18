<?php


namespace Ling\Light_Kit_Store\Api\Custom\Classes;

use Ling\Light_Kit_Store\Api\Generated\Classes\UserHasItemApi;
use Ling\Light_Kit_Store\Api\Custom\Interfaces\CustomUserHasItemApiInterface;



/**
 * The CustomUserHasItemApi class.
 */
class CustomUserHasItemApi extends UserHasItemApi implements CustomUserHasItemApiInterface
{


    /**
     * Builds the CustomUserHasItemApi instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
