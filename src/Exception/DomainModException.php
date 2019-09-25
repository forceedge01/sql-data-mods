<?php

namespace Genesis\SQLExtensionWrapper\Exception;

use Exception;

/**
 * DomainModException class.
 */
class DomainModException extends Exception
{
    /**
     * @param mixed $domainModRef
     * @param mixed $message
     */
    public function __construct($domainModRef, $message)
    {
        parent::__construct('[DomainMod $domainModRef]: ' . $message);
    }
}
