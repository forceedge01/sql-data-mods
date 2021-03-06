<?php

namespace Genesis\SQLExtensionWrapper\Tests;

use Behat\Gherkin\Node\TableNode;
use DateTime;
use Genesis\SQLExtensionWrapper\DataRetriever;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DataRetrieverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DataRetrieverInterface The object to be tested.
     */
    private $testObject;

    /**
     * @var ReflectionClass The reflection class.
     */
    private $reflection;

    /**
     * @var array The test object dependencies.
     */
    private $dependencies = [];

    /**
     * Set up the testing object.
     */
    public function setUp()
    {
        $this->dependencies = [];

        $this->reflection = new ReflectionClass(DataRetriever::class);
        $this->testObject = $this->reflection->newInstanceArgs($this->dependencies);
    }

    /**
     * testGetRequiredData Test that getRequiredData executes as expected.
     */
    public function testGetRequiredData()
    {
        $data = [
            'name' => 'Abdul',
            'dob' => '10-05-1989',
            'lastname' => 'Qureshi'
        ];
        $key = 'dob';
    
        // Execute
        $result = DataRetriever::getRequiredData($data, $key);
    
        // Assert Result
        self::assertEquals($data[$key], $result);
    }

    /**
     * testGetRequiredData Test that getRequiredData executes as expected.
     */
    public function testGetRequiredDataFormattingApplied()
    {
        $data = [
            'name' => 'Abdul',
            'Dob Date' => '10-05-1989',
            'Paid Amount' => 500,
            'lastname' => 'Qureshi'
        ];
        $key1 = 'Dob Date';
        $key2 = 'Paid Amount';
    
        // Execute
        $result = DataRetriever::getRequiredData($data, $key1, DataRetriever::FORMAT_ON);
    
        // Assert Result
        self::assertEquals('1989-05-10 00:00:00', $result);


        // Execute
        $result = DataRetriever::getRequiredData($data, $key2, DataRetriever::FORMAT_ON);
    
        // Assert Result
        self::assertEquals(50000, $result);
    }

    /**
     * testGetRequiredData Test that getRequiredData executes as expected.
     *
     * @expectedException Exception
     */
    public function testGetRequiredDataKeyNotFound()
    {
        $data = [
            'name' => 'Abdul',
            'dob' => '10-05-1989',
            'lastname' => 'Qureshi'
        ];
        $key = 'notfound';
    
        // Execute
        DataRetriever::getRequiredData($data, $key);
    }

    /**
     * testGetOptionalData Test that getOptionalData executes as expected.
     */
    public function testGetOptionalData()
    {
        $data = [
            'name' => 'Abdul',
            'dob' => '10-05-1989',
            'lastname' => 'Qureshi'
        ];
        $key = 'dob';
    
        // Execute
        $result = DataRetriever::getOptionalData($data, $key);
    
        // Assert Result
        self::assertEquals($data[$key], $result);
    }

    /**
     * testGetOptionalData Test that getOptionalData executes as expected.
     */
    public function testGetOptionalDataKeyNotFound()
    {
        $data = [
            'name' => 'Abdul',
            'dob' => '10-05-1989',
            'lastname' => 'Qureshi'
        ];
        $key = 'notfound';
    
        // Execute
        $result = DataRetriever::getOptionalData($data, $key);
    
        // Assert Result
        self::assertNull($result);
    }

    /**
     * testGetOptionalData Test that getOptionalData executes as expected.
     */
    public function testGetOptionalDataKeyNotFoundWithDefault()
    {
        $data = [
            'name' => 'Abdul',
            'dob' => '10-05-1989',
            'lastname' => 'Qureshi'
        ];
        $key = 'notfound';
        $defaultValue = 'defaultvalue';
    
        // Execute
        $result = DataRetriever::getOptionalData($data, $key, $defaultValue);
    
        // Assert Result
        self::assertEquals($defaultValue, $result);
    }

    /**
     * testGetOptionalData Test that getOptionalData executes as expected.
     */
    public function testGetOptionalDataFormattingApplied()
    {
        $data = [
            'name' => 'Abdul',
            'Dob Date' => '10-05-1989',
            'Paid Amount' => 500,
            'lastname' => 'Qureshi'
        ];
        $key1 = 'Dob Date';
        $key2 = 'Paid Amount';
    
        // Execute
        $result = DataRetriever::getOptionalData($data, $key1, false, DataRetriever::FORMAT_ON);
    
        // Assert Result
        self::assertEquals('1989-05-10 00:00:00', $result);


        // Execute
        $result = DataRetriever::getOptionalData($data, $key2, false, DataRetriever::FORMAT_ON);
    
        // Assert Result
        self::assertEquals(50000, $result);
    }

    /**
     * testLoopMultiTable Test that loopMultiTable executes as expected.
     */
    public function testLoopMultiTable()
    {
        $tableNode = new TableNode([
            ['Field1', 'Field2 Date', 'Field3 Amount'],
            ['Abdul', '10-05-1989', '500'],
            ['Chris', '25-07-1987', '700'],
            ['Pam', '12-06-2012', '900']
        ]);

        $expectedResultSet = [[
            'Field1' => 'Abdul',
            'Field2 Date' => '10-05-1989',
            'Field3 Amount' => '500'
        ], [
            'Field1' => 'Chris',
            'Field2 Date' => '25-07-1987',
            'Field3 Amount' => '700'
        ], [
            'Field1' => 'Pam',
            'Field2 Date' => '12-06-2012',
            'Field3 Amount' => '900'
        ]];
    
        // Execute
        $result = DataRetriever::loopMultiTable($tableNode, function ($row, $valueSet) use ($expectedResultSet) {
            // Assert Result
            self::assertEquals($expectedResultSet[$row], $valueSet);

            return 'the result is returned';
        });

        self::assertEquals([
            'the result is returned',
            'the result is returned',
            'the result is returned'
        ], $result);
    }

    /**
     * testLoopSingleTable Test that loopSingleTable executes as expected.
     */
    public function testLoopSingleTable()
    {
        $tableNode = new TableNode([
            ['Field', 'Value'],
            ['Name', 'Abdul'],
            ['DOB Date', '10-05-1989'],
            ['Paid Amount', '500']
        ]);

        $expectedResultSet = [
            ['Field', 'Value'],
            ['Name', 'Abdul'],
            ['DOB Date', '10-05-1989'],
            ['Paid Amount', 500]
        ];

        // Execute
        $result = DataRetriever::loopSingleTable($tableNode, function ($row, $column, $value) use ($expectedResultSet) {
            // Assert Result
            self::assertEquals($expectedResultSet[$row][0], $column);
            self::assertEquals($expectedResultSet[$row][1], $value);

            return 'the result is returned';
        });

        self::assertEquals([
            'the result is returned',
            'the result is returned',
            'the result is returned',
            'the result is returned'
        ], $result);
    }

    /**
     * testLoopPageFieldsTable Test that loopPageFieldsTable executes as expected.
     */
    public function testLoopPageFieldsTable()
    {
        $tableNode = new TableNode([
            ['Field', 'Value'],
            ['Name', 'Abdul'],
            ['DOB Date', '10-05-1989'],
            ['Paid Amount', '500']
        ]);

        $expectedResultSet = [
            ['Field' => 'Name', 'Value' => 'Abdul'],
            ['Field' => 'DOB Date', 'Value' => '10-05-1989'],
            ['Field' => 'Paid Amount', 'Value' => '500']
        ];

        // Execute
        $result = DataRetriever::loopPageFieldsTable($tableNode, function ($row, $valueSet) use ($expectedResultSet) {
            // Assert Result
            self::assertEquals($expectedResultSet[$row], $valueSet);

            return 'the result is returned';
        });

        self::assertEquals([
            'the result is returned',
            'the result is returned',
            'the result is returned'
        ], $result);
    }

    /**
     * testTransformTableNodeToSingleDataSet Test that transformTableNodeToSingleDataSet executes as expected.
     */
    public function testTransformTableNodeToSingleDataSet()
    {
        /*
        | Invoice Due Date | +6 months |
        | Balance Amount   | 500       |
        | Total Amount     | 600       |
        | Departure Date   | +8 months |
         */
        
        // Prepare / Mock
        $dataset = new TableNode([
            ['Invoice Due Date', '+6 months'],
            ['Balance Amount', '500'],
            ['Total Amount', '600'],
            ['Departure Date', '+8 months']
        ]);
    
        // Execute
        $result = DataRetriever::transformTableNodeToSingleDataSet($dataset);

        // Assert Result
        self::assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result['Invoice Due Date']);
        self::assertEquals($result['Balance Amount'], 50000);
        self::assertEquals($result['Total Amount'], 60000);
        self::assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result['Departure Date']);
    }

    /**
     * testTransformTableNodeToMultiDataSets Test that transformTableNodeToMultiDataSets executes as expected.
     */
    public function testTransformTableNodeToMultiDataSets()
    {
        // Prepare / Mock
        $datasets = new TableNode([
            ['Invoice Due Date', 'Balance Amount', 'Total Amount', 'Departure Date'],
            ['+6 months', '500', '600', '+8 months']
        ]);
    
        // Execute
        $result = DataRetriever::transformTableNodeToMultiDataSets($datasets);
    
        // Assert Result
        self::assertEquals(60000, $result[0]['Total Amount']);
        self::assertEquals(50000, $result[0]['Balance Amount']);
        self::assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result[0]['Departure Date']);
        self::assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result[0]['Invoice Due Date']);
    }

    /**
     * testGetFormattedValue Test that getFormattedValue executes as expected.
     */
    public function testGetFormattedValueNoMatch()
    {
        // Execute
        $result = DataRetriever::getFormattedValue('abc', 'field');
    
        // Assert Result
        self::assertEquals('abc', $result);
    }

    /**
     * testGetFormattedValue Test that getFormattedValue executes as expected.
     */
    public function testGetFormattedValueDate()
    {
        // Execute
        $result = DataRetriever::getFormattedValue('10-05-1989', 'Date Of Birth');
    
        // Assert Result
        self::assertEquals('1989-05-10 00:00:00', $result);
    }

    /**
     * testGetFormattedValue Test that getFormattedValue executes as expected.
     */
    public function testGetFormattedValueAmount()
    {
        // Execute
        $result = DataRetriever::getFormattedValue(500, 'Paid Amount');
    
        // Assert Result
        self::assertEquals(50000, $result);
    }
}
