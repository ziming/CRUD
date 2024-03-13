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
     * @return array
     */
    public static function getForTable(string $connection, string $table)
    {
        self::generateDatabaseSchema($connection, $table);

        return self::$schema[$connection][$table] ?? [];
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
        if (! isset(self::$schema[$connection]) || ! isset(self::$schema[$connection][$table])) {
            self::$schema[$connection] = self::mapTables($connection);
        }
    }

    /**
     * Map the tables from raw db values into an usable array.
     *
     * @param  Doctrine\DBAL\Schema\Schema  $rawTables
     * @return array
     */
    private static function mapTables(string $connection)
    {
        return LazyCollection::make(self::getSchemaManager($connection)->getTables())->mapWithKeys(function ($table, $key) use ($connection) {
            $tableName = is_array($table) ? $table['name'] : $table->getName();
            if (is_array($table)) {
                $columns = self::getSchemaManager($connection)->getColumns($tableName);
                $table = new Table($columns);
            }

            return [$tableName => $table];
        })->toArray();
    }

    private static function getSchemaManager(string $connection)
    {
        $connection = DB::connection($connection);

        return method_exists($connection, 'getDoctrineSchemaManager') ? $connection->getDoctrineSchemaManager()->createSchema() : $connection->getSchemaBuilder();
    }
}
