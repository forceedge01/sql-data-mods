<?php

namespace Genesis\SQLExtensionWrapper;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Exception;
use Genesis\SQLExtension\Context\Debugger;
use Genesis\SQLExtensionWrapper\Exception\DataModNotFoundException;
use Genesis\SQLExtensionWrapper\Exception\DomainModNotFoundException;
use UnexpectedValueException;

/**
 * DecoratedSQLContext class. This class gives you context step definitions out of the box that work with your
 * data modules mapping. To use set the appropriate mapping i.e dataMod => namespacedClass and give it a spin.
 */
class DataModSQLContext implements Context
{
    const DEFAULT_NAMESPACE = '\\DataMod\\';
    const DEFAULT_DOMAIN_NAMESPACE = '\\DomainMod\\';

    /**
     * @var array
     */
    private static $dataModMapping = [];

    /**
     * @var array
     */
    private static $domainModMapping = [];

    /**
     * @var string
     */
    private static $userUniqueRef;

    /**
     * @var boolean
     */
    private static $setFailStates = false;

    /**
     * @param boolean $debug
     * @param string  $userUniqueRef Will be appended to new data created to separate data based on users.
     *                               Best to limit it to 2 characters.
     */
    public function __construct($debug = false, $userUniqueRef = null)
    {
        if ($debug) {
            Debugger::enable($debug);
        }

        self::$userUniqueRef = $userUniqueRef;
    }

    /**
     * @BeforeScenario
     *
     * @return void
     * @param  mixed $beforeScenario
     */
    public function clearStore($beforeScenario)
    {
        BaseProvider::getApi()->get('keyStore')->reset();
    }

    /**
     * @param bool $bool
     */
    public function setFailStates($bool)
    {
        self::$setFailStates = $bool;
    }

    /**
     * @AfterStep
     */
    public function setFailureStatesOnFailure()
    {
        // Do stuff here.
    }

    /**
     * @Given I have a/an :domainModRef domain fixture with the following data set:
     * @Given I have a/an :domainModRef domain fixture
     *
     * @return string
     * @param  mixed      $domainModRef
     * @param  null|mixed $uniqueColumn
     */
    public function givenIADomainFixture($domainModRef, TableNode $where = null)
    {
        $data = [];
        if ($where) {
            $data = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $domainMod = $this->getDomainMod($domainModRef);
        $dataMods = $domainMod::getDataMods($data);

        foreach ($dataMods as $dataMod) {
            if (!class_exists($dataMod)) {
                throw new \Exception("DataMod '$dataMod' for DomainMod '$domainModRef' not found.");
            }

            $mapping = BaseProvider::resolveAliasing($dataMod::getDataMapping());
            $modData = array_intersect_key($data, $mapping);
            list($uniqueKey, $dataSet) = $this->getUniqueKeyFromDataset($modData);

            try {
                $dataMod::createFixture(
                    $dataSet,
                    $uniqueKey
                );
            } catch (\Exception $e) {
                throw new Exception\DomainModException($domainModRef, $dataMod, $e->getMessage());
            }
        }
    }

    /**
     * @Given I have additional :domainModRef domain fixture with the following data set:
     * @Given I have additional :domainModRef domain fixture
     *
     * @return string
     * @param  mixed  $domainModRef
     */
    public function givenIHaveAdditionalDomainFixture($domainModRef, TableNode $where = null)
    {
        $data = [];
        if ($where) {
            $data = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $domainMod = $this->getDomainMod($domainModRef);
        $dataMods = $domainMod::getDataMods($data);

        foreach ($dataMods as $dataMod) {
            if (!class_exists($dataMod)) {
                throw new \Exception("DataMod '$dataMod' for DomainMod '$domainModRef' not found.");
            }

            $mapping = BaseProvider::resolveAliasing($dataMod::getDataMapping());
            $modData = array_intersect_key($data, $mapping);
            list($uniqueKey, $dataSet) = $this->getUniqueKeyFromDataset($modData);

            try {
                $dataMod::insert(
                    $dataSet
                );
            } catch (\Exception $e) {
                throw new Exception\DomainModException($domainModRef, $dataMod, $e->getMessage());
            }
        }
    }

    /**
     * @Given I have a/an :dataModRef fixture
     * @Given I have a/an :dataModRef fixture with the following data set:
     * @Given I have a/an :dataModRef fixture with the following data set having unique :uniqueColumn:
     *
     * Note: The first row value in the TableNode is considered the unique key.
     *
     * @param string     $dataModRef
     * @param TableNode  $where
     * @param null|mixed $uniqueColumn
     */
    public function givenIACreateFixture($dataModRef, $uniqueColumn = null, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);

        // You don't need to necessarily have a where clause to create a fixture.
        if ($where) {
            $where = DataRetriever::transformTableNodeToSingleDataSet($where);
        }
        list($uniqueKey, $dataSet) = $this->getUniqueKeyFromDataset($where);

        $dataMod::createFixture(
            $dataSet,
            $uniqueColumn ? $uniqueColumn : $uniqueKey
        );
    }

    /**
     * @Given I have additional :dataModRef fixture
     * @Given I have additional :dataModRef fixture with the following data set:
     *
     * Note: Additional fixture calls do not delete data only add them.
     *
     * @param string    $dataModRef
     * @param TableNode $where
     */
    public function givenIHaveAdditionalACreateFixture($dataModRef, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);

        // You don't need to necessarily have a where clause to create a fixture.
        if ($where) {
            $where = DataRetriever::transformTableNodeToSingleDataSet($where);
        }
        list($uniqueKey, $dataSet) = $this->getUniqueKeyFromDataset($where);

        $dataMod::insert(
            $dataSet
        );
    }

    /**
     *
     * @return string
     */
    private function getUniqueKeyFromDataset(array $data = null)
    {
        $uniqueKey = null;
        $dataSet = array();
        if ($data) {
            $dataSet = $data;
            $uniqueKey = key($dataSet);

            if (! is_numeric($dataSet[$uniqueKey])) {
                $dataSet[$uniqueKey] .= self::$userUniqueRef;
            }
        }

        return [$uniqueKey, $dataSet];
    }

    /**
     * @Given I have an existing :dataModRef fixture with the following data set:
     *
     * @param TableNode|null $where
     *
     * @return string
     */
    public function givenIHaveAnExistingFixture(string $dataModRef, TableNode $where)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSet = DataRetriever::transformTableNodeToSingleDataSet($where);
        $dataMod::select($dataSet);
    }

    /**
     * @Given I have :count :dataModRef fixtures
     * @Given I have :count :dataModRef fixtures with the following data set:
     *
     * @return void
     */
    public function givenIHaveXFixtures(int $count, string $dataModRef, TableNode $where = null)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->givenIACreateFixture($dataModRef, $where);
        }
    }

    /**
     * @Given I have multiple :dataModRef fixtures with the following data set(s):
     * @Given I have multiple :dataModRef fixtures with the following data set(s) having unique :uniqueColumn:
     *
     * Note: The first column value in the TableNode is considered the unique key.
     *
     * @param string     $dataModRef
     * @param null|mixed $uniqueColumn
     */
    public function givenIMultipleCreateFixtures($dataModRef, $uniqueColumn = null, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSets = DataRetriever::transformTableNodeToMultiDataSets($where);

        foreach ($dataSets as $dataSet) {
            $uniqueKey = key($dataSet);

            if (! is_numeric($dataSet[$uniqueKey])) {
                $dataSet[$uniqueKey] .= self::$userUniqueRef;
            }

            $dataMod::createFixture(
                $dataSet,
                $uniqueColumn ? $uniqueColumn : $uniqueKey
            );
        }

        BaseProvider::getApi()->setKeyword(strtolower($dataModRef) . '_set', $dataSets);
    }

    /**
     * @Given I have additional multiple :dataModRef fixtures with the following data set(s):
     *
     * Note: Additional calls do not delete data first, only add them.
     *
     * @param string $dataModRef
     */
    public function givenIHaveAdditionalMultipleCreateFixtures($dataModRef, TableNode $where)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSets = DataRetriever::transformTableNodeToMultiDataSets($where);

        foreach ($dataSets as $dataSet) {
            $dataMod::insert(
                $dataSet
            );
        }

        BaseProvider::getApi()->setKeyword(strtolower($dataModRef) . '_set', $dataSets);
    }

    /**
     * @Given I do not have the following fixtures:
     */
    public function doNotHaveTheFollowingFixtures(TableNode $dataMods)
    {
        foreach ($dataMods->getRowsHash() as $dataModRef => $unused) {
            $this->iDoNotHaveAFixtureWithTheFollowingDataSet($dataModRef);
        }
    }

    /**
     * @Given I do not have a/any :dataModRef fixture(s)
     * @Given I do not have a/any :dataModRef fixture(s) with the following data set:
     * @param mixed $dataModRef
     */
    public function iDoNotHaveAFixtureWithTheFollowingDataSet($dataModRef, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSet = [];
        if ($where) {
            $dataSet = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $dataMod::delete($dataSet);
    }

    /**
     * Useful when testing against API's etc.
     *
     * @Then I should have a :dataModRef
     * @Then I should have a :dataModRef with the following data set:
     * @param mixed $dataModRef
     */
    public function iShouldHaveAWithTheFollowingDataSet($dataModRef, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSet = [];
        if ($where) {
            $dataSet = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $dataMod::assertExists($dataSet);
    }

    /**
     * Useful when testing against API's etc.
     *
     * @Then I should have :expected :dataModRef count
     * @Then I should have :expected :dataModRef count with the following data set:
     * @param mixed $expected
     * @param mixed $dataModRef
     */
    public function iShouldHaveCountTheFollowingDataSet($expected, $dataModRef, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSet = [];
        if ($where) {
            $dataSet = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $actual = $dataMod::count($dataSet);
        if ((int) $expected !== $actual) {
            throw new UnexpectedValueException(sprintf(
                '%s data mod - Expected count "%d", got "%d"',
                $dataModRef,
                $expected,
                $actual
            ));
        }
    }

    /**
     * Useful when testing against API's etc.
     *
     * @Then I should not have a :dataModRef
     * @Then I should not have a :dataModRef with the following data set:
     * @param mixed $dataModRef
     */
    public function iShouldNotHaveAWithTheFollowingDataSet($dataModRef, TableNode $where = null)
    {
        $dataMod = $this->getDataMod($dataModRef);
        $dataSet = [];
        if ($where) {
            $dataSet = DataRetriever::transformTableNodeToSingleDataSet($where);
        }

        $dataMod::assertNotExists($dataSet);
    }

    /**
     * @Given I save the id as :key
     * @param mixed $key
     */
    public function iSaveTheIdAs($key)
    {
        BaseProvider::getApi()->setKeyword($key, BaseProvider::getApi()->getLastId());

        return $this;
    }


    private static function setDataModMappingFromBehatYamlFile(array $dataModMapping = array())
    {
        if (! $dataModMapping) {
            return false;
        }

        self::setDataModMapping($dataModMapping);
    }


    public static function setDataModMapping(array $mapping)
    {
        self::$dataModMapping = $mapping;
    }


    public static function setDomainModMapping(array $mapping)
    {
        self::$domainModMapping = $mapping;
    }

    /**
     * @param string $dataModRef
     *
     * @return DataModInterface
     */
    private function getDataMod($dataModRef)
    {
        $dataMod = $this->resolveDataMod($dataModRef);

        if (! class_exists($dataMod)) {
            throw new DataModNotFoundException($dataMod, self::$dataModMapping);
        }

        return $dataMod;
    }

    /**
     * @param string $domainModRef
     *
     * @return string
     */
    private function getDomainMod($domainModRef)
    {
        $domainMod = $this->resolveDomainMod($domainModRef);

        if (! class_exists($domainMod)) {
            throw new DomainModNotFoundException($domainMod, self::$domainModMapping);
        }

        return $domainMod;
    }

    /**
     * @param string $dataModRef
     *
     * @return string
     */
    private function resolveDataMod($dataModRef)
    {
        // If we found a custom datamod mapping use that.
        if (isset(self::$dataModMapping[$dataModRef])) {
            return self::$dataModMapping[$dataModRef];
        }

        // If we've got a global namespace where all the datamods reside, just use that.
        if (isset(self::$dataModMapping['*'])) {
            return self::$dataModMapping['*'] . $dataModRef;
        }

        return self::DEFAULT_NAMESPACE . $dataModRef;
    }

    /**
     * @param string $domainModRef
     *
     * @return string
     */
    private function resolveDomainMod($domainModRef)
    {
        // If we found a custom datamod mapping use that.
        if (isset(self::$domainModMapping[$domainModRef])) {
            return self::$domainModMapping[$domainModRef];
        }

        // If we've got a global namespace where all the datamods reside, just use that.
        if (isset(self::$domainModMapping['*'])) {
            return self::$domainModMapping['*'] . $domainModRef;
        }

        return self::DEFAULT_DOMAIN_NAMESPACE . $domainModRef;
    }
}
