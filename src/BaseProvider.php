<?php

namespace Genesis\SQLExtensionWrapper;

use Exception;

use Genesis\SQLExtension\Context\Interfaces;

/**
* This class serves as a Decorator for the Genesis API.
* To use this class effectively, create separate classes for each of your tables and extend off this class.
*/
abstract class BaseProvider implements APIDecorator
{
    private $api;

    public function __construct(Interfaces\APIInterface $api)
    {
    	$this->api = $api;

        $this->insertSeedDataIfExists();
    }

    abstract public function getBaseTable();

    abstract public function getDataMapping();

    public function insertSeedDataIfExists()
    {
        if (method_exists($this, 'setupSeedData')) {
            // This will kick off seed data insertion from the constructor.
            $this->insertSeedData($this->setupSeedData());
        }
    }

    private function insertSeedData(array $seedData)
    {
        foreach ($seedData as $table => $individualSeedData) {
            if (! is_string($table)) {
                $table = $this->getBaseTable();
            }

            if (! is_array($individualSeedData)) {
                throw new Exception("Provided data '$individualSeedData' invalid, must be an array.");
            }

            $this->api->insert($table, $individualSeedData);
        }
    }

    public function getKeyword($key)
    {
    	$this->api->get('keyStore')->getKeyword($this->getBaseTable() . '.' . $this->getFieldMapping($key));
    }

    public function truncate()
    {
    	$this->ensureBaseTable();

    	$this->api->delete($this->getBaseTable(), [
    		'id' => '!NULL'
    	]);
    }

    public function createFixture($data = [])
    {
        $this->api->insert($this->getBaseTable(), $data);

        return $this->api->getLastId();
    }

    protected function getFieldMapping($key)
    {
    	if (! isset($this->getDataMapping()[$key]) {
    		throw new Exception("No data mapping provided for key $key");
    	}

    	return $this->getDataMapping()[$key];
    }

    protected function getRequiredData(array $data, $key)
    {
    	if (! array_key_exists($key, $data)) {
    		throw new Exception("Expect to have key '$key' provided.");
    	}

    	return $data[$key];
    }

    protected function getOptionalData(array $data, $key, $default = null)
    {
    	if (! array_key_exists($key, $data)) {
    		return $default;
    	}

    	return $data[$key];
    }

    private function ensureBaseTable()
    {
    	if (! $this->getBaseTable()) {
    		throw new Exception('This call requires the getBaseTable to return the table to operate on.');
    	}
    }
}