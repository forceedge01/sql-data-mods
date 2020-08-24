<?php

namespace DataMod;

use Genesis\DataMods\Traits\SimplifiedDeclarations;
use Genesis\DataMods\Traits\SimplifiedDefaults;
use Genesis\SQLExtensionWrapper\BaseProvider;
use Genesis\SQLExtensionWrapper\Contract\DataModInterface;

/**
 * Address class. This class implements the simplified traits that shorten the syntax and may be more compatible looking 
 * with frameworks such as laravel.
 */
class Address extends BaseProvider implements DataModInterface
{
    use SimplifiedDeclarations;
    use SimplifiedDefaults;

    /**
     * Get the base table name.
     */
    private static $baseTable = 'Address';

    /**
     * Get data mapping of the table.
     */
    private static $dataMapping = [
        'id' => 'id',
        'user_id' => 'user_id',
        'address' => 'address',
    ];

    /**
     * Get delete defaults.
     */
    private static $deleteDefaults = [
        'id' => DataModInterface::NOT_NULL
    ];

    /**
     * @param array $data
     *
     * @return array
     */
    public static function getInsertDefaults(array $data = [])
    {
        return [
            'user_id' => User::getRequiredValue('id')
        ];
    }
}