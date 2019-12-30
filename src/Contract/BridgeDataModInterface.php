<?php

namespace Genesis\SQLExtensionWrapper\Contract;

/**
 * Genesis Bridge interface.
 */
interface BridgedDataModInterface
{
    /**
     * The class that holds the table and property names.
     *
     * @return string
     */
    public static function getBridgedClass();
}
