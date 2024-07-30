<?php

namespace Backpack\CRUD\app\Models\Traits;

use Illuminate\Support\Arr;

trait HasIdentifiableAttribute
{
    /**
     * Get the name of the attribute that best defines the entry, from the user perspective.
     *
     * Rephrased: In most cases a user will NOT identify an Article because its ID is "4", but
     * because its name is "10 Ways to Burn Fat". This method returns the column in the database
     * that represents what is better to show to the user as an identifier rather than the ID.
     * Ex: name, title, label, description etc.
     *
     * @return string The name of the column that best defines this entry from the user perspective.
     */
    public function identifiableAttribute()
    {
        if (property_exists($this, 'identifiableAttribute')) {
            return $this->identifiableAttribute;
        }

        return static::guessIdentifiableColumnName();
    }

    /**
     * Get the most likely column in the db table that could be used as an identifiable attribute.
     *
     * @return string The name of the column in the database that is most likely to be a good identifying attribute.
     *
     * @throws \Exception
     */
    private static function guessIdentifiableColumnName()
    {
        $instance = new static();
        $connection = $instance->getConnectionWithExtraTypeMappings();
        $table = $instance->getTableWithPrefix();
        $columnNames = app('DatabaseSchema')->listTableColumnsNames($connection->getName(), $table);
        $indexes = app('DatabaseSchema')->listTableIndexes($connection->getName(), $table);

        // these column names are sensible defaults for lots of use cases
        $sensibleDefaultNames = ['name', 'title', 'description', 'label'];

        // if any of the sensibleDefaultNames column exists
        // that's probably a good choice
        foreach ($sensibleDefaultNames as $defaultName) {
            if (in_array($defaultName, $columnNames)) {
                return $defaultName;
            }
        }

        // if none of the sensible defaults exists
        // we get the first column from database
        // that is NOT indexed (usually primary, foreign keys)
        foreach ($columnNames as $columnName) {
            if (! in_array($columnName, $indexes)) {
                //check for convention "field<_id>" in case developer didn't add foreign key constraints.
                if (strpos($columnName, '_id') !== false) {
                    continue;
                }

                return $columnName;
            }
        }

        // in case everything fails we just return the first column in database
        $firstColumnInTable = Arr::first($columnNames);
        if (! empty($firstColumnInTable)) {
            return $firstColumnInTable;
        }

        // if there are no columns in the table, we need to throw an exception as there is nothing we can use to
        // correlate with the entry. Developer need to tell Backpack what attribute to use.
        throw new \Exception("There are no columns in the table «{$table}». Please add a column to the table or define a 'public function identifiableAttribute()' in the model.");
    }
}
