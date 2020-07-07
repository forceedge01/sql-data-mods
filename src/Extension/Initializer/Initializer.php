<?php

namespace Genesis\SQLExtensionWrapper\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Genesis\SQLExtensionWrapper\DataModSQLContext;

/**
 * ContextInitialiser class.
 */
class Initializer implements ContextInitializer
{
    /**
     * @var array
     */
    private $dataModMapping = [];

    /**
     * @var array
     */
    private $domainModMapping = [];


    public function __construct(
        array $dataModMapping = [],
        array $domainModMapping = []
    ) {
        $this->dataModMapping = $dataModMapping;
        $this->domainModMapping = $domainModMapping;
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        return [
            'dataMod' => $this->dataModMapping,
            'domainMod' => $this->domainModMapping
        ];

    }


    public function initializeContext(Context $context)
    {
        if (is_a($context, 'FailAid\\Context\\FailureContext')) {
            DataModSQLContext::setFailStates(true);
        }

        if ($context instanceof DataModSQLContext) {
            $context::setDataModMapping($this->dataModMapping);
            $context::setDomainModMapping($this->domainModMapping);
        }
    }
}
