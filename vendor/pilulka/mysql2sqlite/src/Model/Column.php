<?php

namespace Pilulka\Mysql2Sqlite\Model;

class Column extends Base
{

    public function parts()
    {
        return [
            $this->name(),
            $this->dataType(),
            $this->isNullable(),
            $this->primaryKey(),
            $this->autoIncrement(),
        ];
    }

    private function name()
    {
        return $this->data['COLUMN_NAME'];
    }

    private function dataType()
    {
        $type = mb_strtolower($this->data['DATA_TYPE']);
        if ($this->isInteger($type)) {
            return 'INTEGER';
        } elseif ($this->isBinary($type)) {
            return 'BLOB';
        } elseif($this->isReal($type)) {
            return 'REAL';
        }
        return 'TEXT';;
    }

    private function isReal($type)
    {
        return in_array(
            $type,
            [
                'decimal', 'float', 'double',
            ]
        );
    }

    private function isInteger($type)
    {
        return in_array(
            $type,
            [
                'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
            ]
        );
    }

    private function isBinary($type)
    {
        return in_array(
            $type,
            [
                'bit', 'binary', 'tinyblob', 'blob', 'mediumblob', 'longblob',
            ]
        );
    }

    private function isNullable()
    {
        $null = '';
        if ($this->data['IS_NULLABLE'] == 'NO') {
            $null .= 'NOT ';
        }
        $null .= 'NULL';
        return $null;
    }

    private function primaryKey()
    {
        return ($this->data['COLUMN_KEY'] == 'PRI')
            ? 'PRIMARY KEY'
            : '';
    }

    private function autoIncrement()
    {
        if($this->data['EXTRA'] == 'auto_increment') {
            return 'AUTOINCREMENT';
        }
    }

}
