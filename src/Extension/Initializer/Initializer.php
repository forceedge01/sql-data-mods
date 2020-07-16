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

    /**
     * @var array
     */
    private $failAidOptions = [];


    public function __construct(
        array $dataModMapping = [],
        array $domainModMapping = [],
        array $failAidOptions = []
    ) {
        $this->dataModMapping = $dataModMapping;
        $this->domainModMapping = $domainModMapping;
        $this->failAidOptions = $failAidOptions;
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
            DataModSQLContext::setFailStates(
                $this->failAidOptions['output']['enabled'],
                $this->failAidOptions['output']
            );
        }

        if ($context instanceof DataModSQLContext) {
            $context::setDataModMapping($this->dataModMapping);
            $context::setDomainModMapping($this->domainModMapping);
        }
    }
}
