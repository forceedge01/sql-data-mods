<?php

namespace Genesis\SQLExtensionWrapper\Exception;

use Exception;

/**
 * DomainModNotFoundException class.
 */
class DomainModNotFoundException extends Exception
{
    /**
     * @param string $domainModRef
     */
    public function __construct($domainModRef, array $paths = [])
    {
        parent::__construct(
            'Unable to find domainMod "'.$domainModRef.'", please make ' .
            'sure the namespace is registered correctly and it exists. Registered domainMod paths: ' .
            print_r($paths, true)
        );
    }
}
