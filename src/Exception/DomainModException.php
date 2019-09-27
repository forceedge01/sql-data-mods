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
     * @param mixed $dataMod
     * @param mixed $message
     */
    public function __construct($domainModRef, $dataMod, $message)
    {
        parent::__construct("[DomainMod $domainModRef::$dataMod]: " . $message);
    }
}
