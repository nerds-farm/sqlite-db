<?php

namespace Pilulka\Mysql2Sqlite\Model;

use Pilulka\Mysql2Sqlite\Schema;

class Table implements Schema
{

    private $table;
    private $columns = [];
    private $indexes = [];
    private $foreigns = [];

    /**
     * Table constructor.
     * @param $table
     * @param array $columns
     * @param array $indexes
     * @param array $foreigns
     */
    public function __construct($table, array $columns, array $indexes, array $foreigns)
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->indexes = $indexes;
        $this->foreigns = $foreigns;
    }


    public function getSchema()
    {
        return <<<SQL
{$this->dropIfExists()}
{$this->createTable()} (
\t{$this->tableBody()}
);
{$this->indexes()}
\n
SQL;

    }


    private function dropIfExists()
    {
        return "DROP TABLE IF EXISTS {$this->tableName()};";
    }

    private function createTable()
    {
        return "CREATE TABLE {$this->tableName()}";
    }

    private function columns()
    {
        $columns = [];
        foreach ($this->columns as $columnData) {
            $columns[] = (string)(new Column($columnData));
        }
        return $columns;
    }

    private function foreigns()
    {
        $foreigns = [];
        if($this->foreigns){
            foreach ($this->foreigns as $foreign) {
                $foreigns[] = (string)(new Foreign($foreign));
            }
        }
        return $foreigns;
    }

    private function indexes()
    {
        $indexes = [];
        foreach ($this->indexes as $indexData) {
            $indexes[] = (string)(new Index($indexData));
        }
        return implode("\n", array_unique($indexes));
    }

    private function tableName()
    {
        return $this->table['TABLE_NAME'];
    }

    /**
     * @return string
     */
    private function tableBody()
    {
        return implode(
            ",\n\t",
            array_merge($this->columns(), $this->foreigns())
        );
    }

}
