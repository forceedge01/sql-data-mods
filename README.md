# SQL Data Mods (Behat Extension) [ ![Codeship Status for forceedge01/sql-api-wrapper](https://app.codeship.com/projects/96302210-ad45-0135-72c6-56f00403434d/status?branch=master)](https://app.codeship.com/projects/257181)

Need fixture data but seed causing headaches? Create fixture seed data on the fly, keep them visible in your scenarios, auto generate data and deal with complex data relationships. 

**Find example usage in the features folder.**

Release details:
----------------
Major: Allow multiple data sources to be defined.

Minor: 
- Allow uniqueColumn to be specified from the step definition rather than assuming first column.
- Get count through dataMod::count() call, truncate call now made public.
- Inject domain mod based defaults through to data mods.

Patch: NA.

Tools provided by this package:
--------------------------------
- Define more than one data source i.e mssql, mysql etc.
- DataModSQLContext - Use your data mods directly with step defintions provided by this class. Just register with the behat.yml
file and you are good to go.
- Decorated API BaseProvider Class - for advanced and easy integration with data modules.
- DataRetriever class - Retrieve data in a robust way and make a solid foundation for your test framework quickly.
- CLI commands to facilitate debugging.
- Auto generation of data mod files with or without database support.

CLI Commands:
-------------

Debug sql only:
- To view sql statements executed by the extension use `--debug-sql` flag.

Debug all:
- To view all activity along with executed statements use `--debug-sql-all` flag.

Generate data mod:
- To generate a simple base data mod use `--dm-generate <comma-separated-tables>` flag.
- To generate with database support use `--dm-generate=<comma-separated-tables> --dm-connection=<connectionName>` flag.
- You may want to explore the `--dm-path` and `--dm-namespace` flags from the help menu as well.

DataModSQLContext
------------------
```gherkin
# Insert single entry for a datamod.
Given I have a "User" fixture
# OR with specific data
Given I have a "User" fixture with the following data set:
| name  | Wahab Qureshi              |
| email | its.inevitable@hotmail.com |

# Insert multiple entries for a datamod.
Given I have multiple "User" fixtures with the following data sets:
| name           | email                       |
| Wahab Qureshi  | its.inevitable@hotmail.com  |
| Sabhat Qureshi | next-gen-coder@hotmail.com  |
| Jawad Qureshi  | to-be-coder@hotmail.com     |
| Another name   | [Users.Email\|Name:Another] |
```

The last value in the list above uses an external reference to fetch the value to be inserted. You can find out more about this by
reading the 'Referencing foreign table values' topic in the behat-sql-extension extension. We do need to escape the pipe character
in this call so it doesn't result in a syntax error when using table nodes.

The createFixture call will attempt to delete the existing record before it creates another one so you always end up
with a fresh copy. As easy as it sounds, foreign key constraints may not let that happen. In cases like these you can
disable foreign key checks on the test database (most of the time you won't need to do this).

Installation
-------------

```
composer require --dev genesis/sql-data-mods
```

Sample configurating in the behat.yml file:

```yaml
default:
    suites:
        default:
            contexts:
                - Genesis\SQLExtensionWrapper\DataModSQLContext:
                    debug: false # 1 for all debug, 2 for only SQL queries.
                    userUniqueRef: aq # Optional
    extensions:
        Genesis\SQLExtensionWrapper\Extension:
            connections:
                mysql:
                    engine: mysql # mssql, pgsql, sqlite...
                    host: '%MYSQL_HOST%' # Resolve environment variable
                    port: 3306
                    dbname: mydb_mysql
                    username: root
                    password: root
                    schema: myschema
                    prefix: dev_
                mssql:
                    engine: mssql # mssql, pgsql, sqlite...
                    host: '%MSSQL_HOST%' # Resolve environment variable
                    port: 1433
                    dbname: mydb_mssql
                    username: root
                    password: root
                    schema: myschema
                    prefix: dev_
            dataModMapping: # Optional
                "*": \QuickPack\DataMod\ # Configure path for all data mods using *.
                "User": \QuickPack\DataMod\User\User # Configure single data mod.
            domainModMapping: # Optional
                "*": \QuickPack\DomainMod\
                "User": \QuickPack\DomainMod\User\User
```

debug - Turns debugging on off.
userUniqueRef: Appends the string onto first column of data provided to the fixture step definitions if its a string. This is so every user has its own unique data if multiple users are targeting a single database.
connections: Your database connection details.
dataModMapping: The autoloading namespace reference to your dataMods. (Optional)
domainModMapping: The autoloading namespace reference of your domainMods. (Optional)

Please note: The extension expects you to have your dataMods located in the `features/bootstrap/DataMod` folder. If you have a different mapping to this, you will have to define your autoload
strategy in the composer.json file or manually require the files in. You can set the mapping in php like so:

You can register the context file through php as well.

```php

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Genesis\SQLExtensionWrapper\DataModSQLContext;
use Genesis\SQLExtensionWrapper\BaseProvider;

class FeatureContext
{
    /**
     * @BeforeSuite
     */
    public static function loadDataModSQLContext(BeforeSuiteScope $scope)
    {
        BaseProvider::setCredentials([
            'mssql' => [
                'engine' => 'dblib',
                'name' => 'databaseName',
                'schema' => 'dbo',
                'prefix' => 'dev_',
                'host' => 'myhost',
                'port' => '1433',
                'username' => 'myUsername',
                'password' => 'myPassword'
            ]
        ]);

        // Default path is \\DataMod\\ which points to features/DataMod/, override this way.
        DataModSQLContext::setDataModMapping([
            '*' => '\\Custom\\DataMod\\'
        ]);

        DataModSQLContext::setDomainModMapping([
            '*' => '\\Custom\\DomainMod\\'
        ]);

        $scope->getEnvironment()->registerContextClass(
            DataModSQLContext::class,
            ['debug' => false]
        );
    }
}
```

BaseProvide Class
------------------

The wrapper provides with powerful tools around the [behat-sql-extension](https://github.com/forceedge01/behat-sql-extension) API class. Methods provided:

- createFixture(array $data = [], string $uniqueColumn = null) // Recreates a record for fresh usage. Overridable from data mod.
- getSingle(array $where) // Returns a single record defined by the mapping.
- getColumn(string $column, array $where) // Returns a single column value from the database.
- getValue(string $key) // Get key value based on mapping.
- truncate() // Truncates a table.
- subSelect(string $column, array $where) // Provides the ability to sub select a column for any query.
- rawSubSelect(string $table, string $column, array $where) // Provides the ability to sub select a column for any query without a data mod.
- saveSession(string $primaryKey) // Save the current session for later re-use.
- restoreSession() // Restore the session saved by saveSession.
- getRequiredData(array $data, string $key, boolean $format) // Extended: Extracts value from an array.
- getOptionalData(array $data, string $key, mixed $default = null, boolean $format = false) // Extended: Optional value from an array, provide default otherwise.
- getFieldMapping(string $key) // Extended: Get field mapping provided in the getDataMapping method.
- getKeyword(string $key) // Get the keyword for mapped key.

Note: All methods provided by the wrapper are static, because they have a global state - we don't need to instantiate this wrapper.

## Example usage

Creating a DataMod to use in your context files. This is as easy as just extending the BaseProvider class from your dataMods.

```php
# User.php
<?php

namespace QuickPack\DataMod\User;

use Genesis\SQLExtensionWrapper\BaseProvider;

class User extends BaseProvider
{
    /**
     * Returns the base table to interact with.
     *
     * @return string
     */
    public static function getBaseTable()
    {
        // Ridiculous naming as we find with most databases.
        return 'MySuperApplication.MyUsersNew';
    }

    /**
     * Returns the data mapping for the base table. This is the data that is allowed to be passed in
     * to the data mod. <input> => <mapping>
     *
     * Note any column mapped to '*' is excluded from the queries and only is a part of the data passed around.
     *
     * @return array
     */
    public static function getDataMapping()
    {
        return [
            'id' => 'user_id',
            'name' => 'f_name',
            'email' => 'electronic_address',
            'dateOfBirth' => 'd_o_b',
            'gender' => 'gender',
            'status' => 'real_status',
            'anythingElse' => '*',
            'somethingElse' => '*',
        ];
    }
}

```

Using DataMods in PHP Code
--------------------------

You can now use your data mods as above or directly using PHP code in step definitions. Using your UserDataMod in your context file.

```php
# FeatureContext.php
<?php

use Exception;
use QuickPack\DataMod\User\User;

/**
 * Ideally you would want to separate the data step definitions from interactive/assertive step definitions.
 * This is for demonstration only.
 */
class FeatureContext
{
    /**
     * @Given I have a User
     *
     * Use the API to create a fixture user.
     */
    public function createUser()
    {
        // This will create a fixture user.
        // The name will be set to 'Wahab Qureshi'. The rest of the fields if required by the database will be autofilled
        // with fixture data, if they are nullable, null will be stored.
        // If the record exists already, it will be deleted based on the 'name' key provided.
        User::createFixture([
            'name' => 'Wahab Qureshi'
        ], 'name');
    }

    /**
     * @Given I have (number) User(s)
     *
     * Use the API to create random 10 users.
     */
    public function create10Users($count)
    {
        // Save this user's session.
        User::saveSession('id');

        // Create 10 random users.
        for($i = 0; $i < 10; $i++) {
            // Store the ids created for these users maybe?
            $this->userIds[] = User::createFixture();
        }

        // Restore session of the user we created above.
        User::restoreSession();
    }

    /**
     * @Given I should see a User
     *
     * Use the API to retrieve the user created.
     */
    public function assertUserOnPage()
    {
        // Assumptions - we ran the following before running this command:
        // Given I have a User
        // And I have 10 Users

        // Retrieve data created, this will reference the user created by 'Given I have a User' as the session was preserved.
        $id = User::getValue('id');
        $name = User::getValue('name');
        $dateOfBirth = User::getValue('dateOfBirth');
        $gender = User::getValue('gender');

        // Assert that data is on the page.
        $this->assertSession()->assertTextOnPage($id);
        $this->assertSession()->assertTextOnPage($name);
        $this->assertSession()->assertTextOnPage($dateOfBirth);
        $this->assertSession()->assertTextOnPage($gender);
    }

    /**
     * @Given I should see (number) User(s) in the list
     *
     * Consumption of the users created above. For illustration purposes only.
     */
    public function assertUserOnPage($number)
    {
        $usersList = $this->getSession()->getPage()->find('css', '#usersListContainer li');
        $actualCount = count($usersList);

        if ($number !== $actualCount) {
            throw new Exception("Expected to have '$number' users, got '$actualCount'");
        }
    }
}

```

Advanced DataModding
--------------------

You can further extend your DataMod with other methods like so:

```php
<?php

namespace QuickPack\DataMod\User;

use Genesis\SQLExtensionWrapper\BaseProvider;

class User extends BaseProvider
{
    ...

    /**
     * If you've defined multiple connections, you can specify which connection to use for each of your
     * data mods.
     *
     * @return string
     */
    public static function getConnectionName()
    {
        return 'mssql';
    }

    /**
     * Special Method: Use this method to create auxiliary data off the initial create. This is suitable
     * for creating data where the tables are fragmented.
     *
     * @param int $id The id of the created record.
     * @param array $data The data that was originally passed to create.
     *
     * @return void
     */
    public static function postCreateHook($id, array $data)
    {
        Meta::createFixture(...);
    }

    /**
     * Special Method: This method if implemented is merged with the data provided.
     * Any data provided overwrites the default data.
     * This is a good opportunity to set foreign key values using the subSelect call.
     *
     * Similar methods are available:
     * - getSelectDefaults()
     * - getUpdateDefaults()
     * - getDeleteDefaults()
     *
     * @param array $data The data passed in to the data mod.
     *
     * @return array
     */
    public static function getInsertDefaults(array $data)
    {
        return [
            'dateOfBirth' => '1989-05-10',
            'gender' => Gender::subSelect('type', ['id' => 1])
        ];
    }

    /**
     * Method uses subSelect to intelligently select the Id of the status and updates the user record.
     * This is a common case where you want your feature files to be descriptive and won't just pass in id's, use
     * descriptive names instead and infer values in the lower layers.
     *
     * @param string $status The status name (enabled/disabled).
     * @param int $userId The user to update.
     *
     * @return void
     */
    public static function updateStatusById($status, $userId)
    {
        self::update(self::getBaseTable(), [
            'status' => BaseProvider::rawSubSelect('Status', 'id', ['name' => $status])
        ], [
            'id' => $userId
        ])
    }
}

```

The getInsertDefaults() method is special, it will be called automatically if it exists. It allows you to set default values
for any column. An example could be a boolean flag of some sort that you don't want to keep defining or want to override 
optionally. Another example could be setting foreign keys correctly or imposing requirements for certain operations. You have
this method for each operator type i.e select, update, insert and delete.

Combining Data mods (Domain Mod)
--------------------------------

Sometimes our data is fragmented between several tables, but we don't want that fragmentation to bleed into our test files.
To facilitate such a scenario, we've got domain mods.

```php
<?php

namespace App\Tests\Behaviour\DomainMod;

use App\Tests\Behaviour\DataMod;
use Genesis\SQLExtensionWrapper\Contract\DomainModInterface;

class User implements DomainModInterface
{
    public static function getDataMods()
    {
        return [
            DataMod\User::class,
            DataMod\UserExt::class,
        ];
    }
}

```

The step definitions to support this feature are:

```yml
Scenario: ...
    Given I have a "Ship" domain fixture
    Given I have an additional "Ship" domain fixture
    Given I have a "Ship" domain fixture with the following data set:
    | name | ABC |
```

You can inject data mod defaults through domain mods which take precedence over data mod defaults but lower than the supplied data through the step definition:

```php
<?php

namespace App\Tests\Behaviour\DomainMod;

use App\Tests\Behaviour\DataMod;
use Genesis\SQLExtensionWrapper\Contract\DomainModInterface;

class User implements DomainModInterface
{
    public static function getInsertDefaults()
    {
        return [
            DataMod\User::class => [
                'age' => 31
            ],
            DataMod\UserExt::class => [
                'name' => 'Jack'
            ],
        ];
    }

    public static function getDataMods()
    {
        return [
            DataMod\User::class,
            DataMod\UserExt::class,
        ];
    }
}

```

Build dynamic URLs
-------------------

You can use the getKeyword call provided by the BaseProvider class to get a reference for a key defined on a dataMod. For example

```php
// We want to create a user and have its id placed in the URL such as '/user/<id>/', so we can visit the page.

// Normally with the above data mod configuration and behat-sql-extension you need to do the following:
$routes = [
    'user' => '/user/{MySuperApplication.MyUsersNew.user_id}/'
];

// Having a data mod gives you a way to abstract any table information 
// by just referencing the data mod itself. The above can be re-written as:
$routes = [
    'user' => '/user/' . User::getKeyword('id') . '/'
];

```
Just keep on using your standard visit page step definition using the genesis/test-routing
```php
    /**
     * @Given I am on the :arg1 page
     * @Given I visit the :arg1 page
     */
    public function iAmOnThePage($arg1)
    {
        $url = Routing::getRoute($arg1, function ($url) {
            return BaseProvider::getApi()->get('keyStore')->parseKeywordsInString($url);
        });
        $this->getMink()->getSession()->visit($url);
    }
```

Advanced Integrations
---------------------

To use a different version of the Api, you will have to make good use of polymorphism. Extend the BaseProvider in your project and implement the abstract method getAPI(). This method needs to return an object that implements Genesis\SQLExtension\Context\Interfaces\APIInterface.

```php
# BaseDataMod.php
<?php

use Genesis\SQLExtensionWrapper\BaseProvider;
use Genesis\SQLExtension\Context;

/**
 * Serves as a base class for your own project, makes refactoring easier if you decide to inject your own version of 
 * the API.
 */
abstract class BaseDataMod extends BaseProvider
{
    /**
     * @var array The connection details the API expects.
     */
    public static $connectionDetails;

    /**
     * @var Context\Interfaces\APIInterface
     */
    private static $sqlApi;

    /**
     * @return Context\Interfaces\APIInterface
     */
    public static function getAPI()
    {
        if (! self::$sqlApi) {
            self::$sqlApi = new Context\API(
                new Context\DBManager(Context\DatabaseProviders\Factory(), self::$connectionDetails),
                new Context\SQLBuilder(),
                new Context\LocalKeyStore(),
                new Context\SQLHistory()
            );
        }

        return self::$sqlApi;
    }
}
```

Then extend your data mods from the above class instead.

Data Retriever Class
--------------------

The data retriever class makes it easy to work with test data sets and provide enough context around parameters passed around.
We all know using array's are a pain. To ease the pain ever so slightly we have the following calls:
- getRequiredData($searchArray, $key) // Implicit data conversion, throws exception when data not provided.
- getOptionalData($searchArray, $key, $defaultValue, $format) // Explicit data conversion.

To ease the pain of working with TableNodes, here are some calls:
- loopMultiTable($tableNode, callbackFunction)
- loopSingleTable($tableNode, callbackFunction)
- loopPageFieldsTable($tableNode, callbackFunction)
- transformTableNodeToSingleDataSet($tableNode)
- transformTableNodeToMultiDataSets($tableNode)

Data conversion built in for most common data types:
- getFormattedValue($value, $fieldName) // Follows the following rules
```gherkin
| Fieldname | Conversion                | More info                                                        |
| %Date%    | Format to Y-m-d H:i:s     | This is particularly useful with dynamic dates such as yesterday |
| %Amount%  | To pence, Multiply by 100 | User friendly input such as 100 amount equals 10000 pence        |
```

Using a Bridge
--------------

You can also set a bridge between your framework data modules and the wrapper. Your bridge must implement the Genesis\SQLExtensionWrapper\Contract\BridgeInterface to work. You can register your bridge like so:

```php
class FeatureContext
{
    public function __construct()
    {
        $bridgeObject = new DoctrineBridge();
        DataModSQLContext::registerBridge($bridgeObject);
    }
}
```

## Development

To get started with development of this project:

### Deployer https://github.com/forceedge01/deployer

When in the root of the project run
```
dep use
```

Then run
```
dep project:dev
```

The above will init and download the vagrant box as the submodule, get the box running, and perform a composer install within.

Running unit tests:

```
dep project:test
```

This will run the unit tests within the vagrant box.
