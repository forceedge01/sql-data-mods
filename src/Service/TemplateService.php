<?php

namespace Genesis\SQLExtensionWrapper\Service;

use Genesis\SQLExtensionWrapper\BaseProvider;

class TemplateService
{
    public function get()
    {
        return file_get_contents(__DIR__ . '/../Template/DataMod.php.txt');
    }

    public static function replaceTemplateVars($table, $namespace, $contents, $fields = null, $connection = null)
    {
        return str_replace([
            '{{DATAMOD}}',
            '{{TABLE}}',
            '{{NAMESPACE}}',
            '{{FIELDS}}',
            '{{CONNECTION_METHOD}}'
        ], [
            BaseProvider::getDataModForTable($table),
            $table,
            $namespace,
            $fields,
            self::getConnectionNameMethod($connection)
        ],
        $contents);
    }

    public static function getConnectionNameMethod($connection)
    {
        if (!$connection) {
            return '';
        }

        return "
    /**
     * @return string
     */
    public static function getConnectionName()
    {
        return '$connection';
    }

";
    }

    public static function getFieldsAsString($primaryKey, array $fields)
    {
        $fieldString = '';
        foreach ($fields as $column => $field) {
            if ($column === $primaryKey && $column !== 'id') {
                $fieldString .= str_repeat(' ', 12) . "'id' => '$primaryKey'," . PHP_EOL;
                continue;
            }

            $humanised = self::humaniseString($column);
            if ($humanised === $column) {
                $fieldString .= str_repeat(' ', 12) . "'$humanised'," . PHP_EOL;
            } else {
                $fieldString .= str_repeat(' ', 12) . "'$humanised' => '$column'," . PHP_EOL;
            }
        }

        return trim($fieldString, PHP_EOL);
    }

    public static function humaniseString($string)
    {
        return str_replace(['_','-'], ' ', strtolower($string));
    }
}
