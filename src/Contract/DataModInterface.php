<?php

namespace Genesis\SQLExtensionWrapper\Contract;

/**
 * Genesis Data Mod interface.
 */
interface DataModInterface
{
    /**
     * To be used in data mapping when there is a need for a placeholder mapping to pass data around and not passed to the database.
     * Example:
     * 'age' => 'age',
     * 'name' => DataModInterface::NOT_MAPPED
     *
     * In the above example the value for age will be passed to the database but not the name.
     */
    const NOT_MAPPED = '*';

    /**
     * Null value syntax for database queries.
     */
    const NULL_VALUE = 'NULL';

    /**
     * Not null value syntax for database queries.
     */
    const NOT_NULL = '!NULL';

    /**
     * Declare the table to interact with.
     *
     * @return string
     */
    public static function getBaseTable();

    /**
     * Mapping to the table. Any columns mapped to '*' will be excluded from the query but the data will be
     * passed around.
     *
     * @return array
     */
    public static function getDataMapping();
}
