<?php


namespace Ling\Light_Kit_Store\Helper;

use Ling\Light_User\LightOpenUser;

/**
 * The LightKitStoreUserHelper class.
 */
class LightKitStoreUserHelper
{

    /**
     * Attaches the desired userRow key/value pairs to the user props.
     *
     *
     * @param LightOpenUser $user
     * @param $userRow
     */
    public static function setUserPropsFromRow(LightOpenUser $user, $userRow)
    {
        /**
         * As for now (early development),
         * we attach everything... I might filter out some props later...
         */
        $user->setProps($userRow);
    }
}