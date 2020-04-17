<?php

namespace Genesis\SQLExtensionWrapper\Exception;

use Exception;

/**
 * DataModNotFoundException class.
 */
class DataModNotFoundException extends Exception
{
    /**
     * @param string $dataModRef
     */
    public function __construct($dataModRef, array $paths = [])
    {
        parent::__construct(
            'Unable to find dataMod "'.$dataModRef.'", please make ' .
            'sure the namespace is registered correctly and it exists. You can auto generate data mods with the --dm-generate command. Look at help menu for more information. Registered dataMod paths: ' .
            print_r($paths, true)
        );
    }
}
