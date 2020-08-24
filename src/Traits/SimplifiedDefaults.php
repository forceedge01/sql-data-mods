<?php

namespace Genesis\DataMods\Traits;

/**
 * Enable alternate syntax for declaring the same information.
 */
trait SimplifiedDefaults
{
    public function getInsertDefaults(array $data)
    {
        return self::$insertDefaults;
    }

    public static function getSelectDefaults(array $data)
    {
        return self::$selectDefaults;
    }

    public static function getDeleteDefaults(array $data)
    {
        return self::$deleteDefaults;
    }

    public static function getUpdateDefaults(array $data)
    {
        return self::$updateDefaults;
    }
}
