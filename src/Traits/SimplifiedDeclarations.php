<?php

namespace Genesis\DataMods\Traits;

/**
 * Enable alternate syntax for declaring the same information.
 * 
 * Use this trait to declare static vars in your datamod to get the same implementation result as the methods.
 * 
 * private static $baseTable = 'user';
 * 
 * private static $dataMapping = ['id' => 'id', 'name' => 'name']
 */
trait SimplifiedDeclarations
{
    public static function getBaseTable()
    {
        return self::$baseTable;
    }
    
    public static function getDataMapping()
    {
        return self::$dataMapping;
    }
}
