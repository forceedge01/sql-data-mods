<?php

namespace Genesis\SQLExtensionWrapper\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Genesis\SQLExtensionWrapper\BaseProvider;
use Genesis\SQLExtensionWrapper\DataModSQLContext;

/**
 * ContextInitialiser class.
 */
class Initializer implements ContextInitializer
{
    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var array
     */
    private $dataModMapping = [];

    /**
     * @param array $connection
     * @param array $dataModMapping
     */
    public function __construct(
        array $connections = [],
        array $dataModMapping = []
    ) {
        $this->connections = $connections;
        $this->dataModMapping = $dataModMapping;
    }

    /**
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof DataModSQLContext) {
            BaseProvider::setCredentials($this->connections);

            $context::setDataModMapping($this->dataModMapping);
        }
    }
}
