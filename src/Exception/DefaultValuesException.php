<?php

namespace Genesis\SQLExtensionWrapper\Exception;

use Exception;

/**
 * DefaultValuesException class.
 */
class DefaultValuesException extends Exception
{
    public function __construct($dataMod, $message, $type = null)
    {
        parent::__construct($dataMod . '::get' . ucfirst($type) . 'Defaults() - ' . $message);
    }
}
