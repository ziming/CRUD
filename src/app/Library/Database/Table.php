<?php

namespace Backpack\CRUD\app\Library\Database;

final class Table
{
    private array $columns;

    public function __construct(array $columns = [])
    {
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

                public function getType()
                {
                    return new class($this->column)
                    {
                        public function __construct(private array $column)
                        {
                        }

                        public function getName()
                        {
                            return $this->column['type'];
                        }
                    };
                }
            };
        }
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn($columnName)
    {
        return isset($this->columns[$columnName]);
    }

    public function getColumn(string $columnName)
    {
        return $this->columns[$columnName];
    }
}
