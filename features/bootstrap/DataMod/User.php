<?php

namespace DataMod;

use Genesis\SQLExtensionWrapper\BaseProvider;
use Genesis\SQLExtensionWrapper\Contract\DataModInterface;

/**
 * User class. Does not use simplified traits i.e full syntax declarations yields the same result.
 */
class User extends BaseProvider implements DataModInterface
{
    /**
     * @return string
     */
    public static function getBaseTable()
    {
        return 'User';
    }

    /**
     * @return array
     */
    public static function getDataMapping()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'date of birth' => 'dob',
            'age' => 'age',
            'hobby' => '*',
        ];
    }

    /**
     * @param array $where
     *
     * @return void
     */
    public static function delete(array $where = [])
    {
        if (! $where) {
            $where = [
                'id' => '!NULL'
            ];
        }

        parent::delete($where);
    }
}
