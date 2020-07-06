<?php

namespace Genesis\SQLExtensionWrapper\Service;

use Genesis\SQLExtensionWrapper\BaseProvider;

class DataModGeneratorService
{
    public static function generate(array $generate, $path, $namespace, $connection)
    {
        echo 'Generating data mods...' . PHP_EOL . PHP_EOL;
        FolderService::createPath($path);
        $templateFile = TemplateService::get();

        $api = null;
        if ($connection !== null) {
            $api = BaseProvider::getApi($connection);
        }

        foreach ($generate as $table) {
            echo $table . PHP_EOL;

            $dataMod = BaseProvider::getDataModForTable($table);
            $filename = FolderService::cleanPath($path, $dataMod . '.php');

            if (is_file($filename)) {
                echo 'Error - DataMod ' . $dataMod . ' already exists for table: ' . $table . PHP_EOL;
                continue;
            }

            $fields = self::getFieldsAsString($table, $api);
            $contents = TemplateService::replaceTemplateVars($table, $namespace, $templateFile, $fields);

            file_put_contents(
                $filename,
                $contents
            );
        }
        echo PHP_EOL . 'DataMods generated.' . PHP_EOL . PHP_EOL;
        exit;
    }

    private static function getFieldsAsString($table, $api)
    {
        $fields = str_repeat(' ', 12) . "'id' => 'id',";
        if ($api) {
            list($primaryKey, $columns) = self::getColumnsAsArray($table, $api);
            $fields = TemplateService::getFieldsAsString($primaryKey, $columns);
        }

        return $fields;
    }

    private static function getColumnsAsArray($table, $api)
    {
        $params = $api->get('dbManager')->getParams();

        $primaryKey = $api->get('dbManager')->getPrimaryKeyForTable(
            $params['DBNAME'],
            $params['DBSCHEMA'],
            $table
        );
        $columns = $api->get('dbManager')->getTableColumns(
            $params['DBNAME'],
            $params['DBSCHEMA'],
            $table
        );

        return [$primaryKey, $columns];
    }

    public static function confirmGenerate(array $generate, $path, $namespace, $connection)
    {
        echo 'About to generate data mods...' . PHP_EOL . PHP_EOL;

        foreach ($generate as $table) {
            $dataMod = BaseProvider::getDataModForTable($table);
            echo 'Table: ' . $table . PHP_EOL;
            echo 'DataMod: ' . $namespace . '\\' . $dataMod . PHP_EOL;
            if ($connection) {
                echo 'Connection: ' . $connection . PHP_EOL;
            }
            echo 'Auto generate fields: ' . ($connection !== null ? 'Y' : 'N') . PHP_EOL;
            echo 'Path: ' . FolderService::cleanPath($path, $dataMod . '.php') . PHP_EOL . PHP_EOL;
        }

        echo '--- Confirm [y/n]';

        $handle = fopen ('php://stdin','r');
        $line = fgets($handle);
        fclose($handle);
        if(strtolower(trim($line)) != 'y'){
            echo 'Aborting...' . PHP_EOL;
            exit;
        };
    }
}
