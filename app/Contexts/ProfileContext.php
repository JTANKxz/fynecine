<?php

namespace App\Contexts;

class ProfileContext
{
    private static $profile = null;

    public static function set($profile)
    {
        self::$profile = $profile;
    }

    public static function get()
    {
        return self::$profile;
    }

    public static function isKids(): bool
    {
        return self::$profile && self::$profile->is_kids;
    }
}
