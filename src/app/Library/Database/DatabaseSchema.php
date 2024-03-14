<?php

namespace Backpack\CRUD\app\Library\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

final class DatabaseSchema
{
    private static $schema;

    /**
     * Return the schema for the table.
     *
     * @param  string  $connection
     * @param  string  $table
     */
    public static function getForTable(string $connection, string $table)
    {
        self::generateDatabaseSchema($connection, $table);
        return self::$schema[$connection][$table] ?? null;
    }

    /**
     * Generates and store the database schema.
     *
     * @param  string  $connection
     * @param  string  $table
     * @return void
     */
    private static function generateDatabaseSchema(string $connection, string $table)
    {
        if (! isset(self::$schema[$connection]) || !isset(self::$schema[$connection][$table])) {
            self::$schema[$connection] = self::mapTables($connection);
        }
    }

    /**
     * Map the tables from raw db values into an usable array.
     *
     * @param  string  $connection
     * @return array
     */
    private static function mapTables(string $connection)
    {
        return LazyCollection::make(self::getCreateSchema($connection)->getTables())->mapWithKeys(function ($table, $key) use ($connection) {
            $tableName = is_array($table) ? $table['name'] : $table->getName();
            
            if(self::$schema[$connection][$tableName] ?? false) {
                return [$tableName => self::$schema[$connection][$tableName]];
            }
            
            if (is_array($table)) {
                $table = new Table(self::mapTableColumns($connection, $tableName));
                
            }

            return [$tableName => $table];
        })->toArray();
    }

    private static function getIndexColumnNames($connection, $table)
    {
        $schemaManager = self::getSchemaManager($connection);
        $indexes = method_exists($schemaManager, 'listTableIndexes') ? $schemaManager->listTableIndexes($table) : $schemaManager->getIndexes($table);

        $indexes = array_map(function ($index) {
            return is_array($index) ? $index['columns'] : $index->getColumns();
        }, $indexes);

        $indexes = \Illuminate\Support\Arr::flatten($indexes);

        return array_unique($indexes);
    }

    private static function mapTableColumns($connection, $table)
    {
        $indexedColumns = self::getIndexColumnNames($connection, $table);

        return LazyCollection::make(self::getSchemaManager($connection)->getColumns($table))->mapWithKeys(function ($column, $key) use ($indexedColumns) {
            $column['index'] = array_key_exists($column['name'], $indexedColumns) ? true : false;

            return [$column['name'] => $column];
        })->toArray();
    }

    private static function getCreateSchema(string $connection)
    {
        $schemaManager = self::getSchemaManager($connection);
        return method_exists($schemaManager, 'createSchema') ? $schemaManager->createSchema() : $schemaManager;
    }

    private static function getSchemaManager(string $connection)
    {
        $connection = DB::connection($connection);

        return method_exists($connection, 'getDoctrineSchemaManager') ? $connection->getDoctrineSchemaManager() : $connection->getSchemaBuilder();
    }

    public function listTableColumnsNames(string $connection, string $table)
    {
        $table = self::getForTable($connection, $table);
        return array_keys($table->getColumns());
    }

    public function listTableIndexes(string $connection, string $table)
    {
        return self::getIndexColumnNames($connection, $table);
    }
}
