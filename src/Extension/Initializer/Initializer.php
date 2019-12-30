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
     * @var array
     */
    private $domainModMapping = [];

    /**
     * @param array $connection
     * @param array $dataModMapping
     * @param array $domainModMapping
     */
    public function __construct(
        array $connections = [],
        array $dataModMapping = [],
        array $domainModMapping = []
    ) {
        $this->connections = $connections;
        $this->dataModMapping = $dataModMapping;
        $this->domainModMapping = $domainModMapping;
    }

    /**
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof DataModSQLContext) {
            BaseProvider::setCredentials($this->connections);

            $context::setDataModMapping($this->dataModMapping);
            $context::setDomainModMapping($this->domainModMapping);
        }
    }
}
