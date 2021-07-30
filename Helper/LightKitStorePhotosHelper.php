<?php


namespace Ling\Light_Kit_Store\Helper;

/**
 * The LightKitStorePhotosHelper class.
 */
class LightKitStorePhotosHelper
{


    /**
     * Returns the first item of type photo from the given screenshots array, or null if no such item was found.
     *
     * @param array $screenshots
     * @return array|null
     */
    public static function getFirstPhotoByItem(array $screenshots): array|null
    {
        foreach ($screenshots as $screenshot) {
            if ('photo' === $screenshot['type']) {
                return $screenshot;
            }
        }
        return null;
    }
}