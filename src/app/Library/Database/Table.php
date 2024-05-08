<?php

namespace Backpack\CRUD\app\Library\Database;

final class Table
{
    private string $name;
    private array $columns = [];
    private array $indexes = [];

    public function __construct(string $name, array $columns = [])
    {
        $this->name = $name;
        foreach ($columns as $column) {
            $this->columns[$column['name']] = new class($column)
            {
                public function __construct(private array $column)
                {
                }

                public function getName()
                {
                    return $this->column['name'];
                }

                public function getNotnull()
                {
                    return ! $this->column['nullable'];
                }

                public function getDefault()
                {
                    return $this->column['default'];
                }

                public function getUnsigned()
                {
                    return in_array('unsigned', explode(' ', $this->column['type']));
                }

                public function getType()
                {
                    return new class($this->column)
                    {
                        public function __construct(private array $column)
                        {
                        }

                        public function getName()
                        {
                            return $this->column['type_name'];
                        }
                    };
                }
            };
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn(string $columnName)
    {
        return isset($this->columns[$columnName]);
    }

    public function getColumn(string $columnName)
    {
        return $this->columns[$columnName];
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function setIndexes(array $indexes)
    {
        $this->indexes = $indexes;
    }
}
