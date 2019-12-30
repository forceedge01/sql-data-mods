<?php

namespace DomainMod;

use DataMod;

/**
 * User class.
 */
class User
{
    public static function getDataMods()
    {
        return [
            DataMod\User::class,
            DataMod\Address::class,
        ];
    }
}
