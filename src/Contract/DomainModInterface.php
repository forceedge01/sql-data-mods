<?php

namespace Genesis\SQLExtensionWrapper\Contract;

/**
 * Genesis Domain Mod interface.
 */
interface DomainModInterface
{
    /**
     * Gets a list of data mod class references and calls the createFixture method on them.
     *
     * @return array
     */
    public static function getDataMods();
}
