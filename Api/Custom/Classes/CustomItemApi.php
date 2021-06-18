<?php


namespace Ling\Light_Kit_Store\Api\Custom\Classes;

use Ling\Light_Kit_Store\Api\Generated\Classes\ItemApi;
use Ling\Light_Kit_Store\Api\Custom\Interfaces\CustomItemApiInterface;



/**
 * The CustomItemApi class.
 */
class CustomItemApi extends ItemApi implements CustomItemApiInterface
{


    /**
     * Builds the CustomItemApi instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
