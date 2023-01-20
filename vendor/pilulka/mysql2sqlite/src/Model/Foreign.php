<?php

namespace Pilulka\Mysql2Sqlite\Model;

class Foreign extends Base
{

    public function parts()
    {
        return [
            'FOREIGN KEY',
            "({$this->data['COLUMN_NAME']})",
            "REFERENCES",
            $this->data['REFERENCED_TABLE_NAME'],
            "({$this->data['REFERENCED_COLUMN_NAME']})",
            "ON DELETE {$this->data['DELETE_RULE']}",
            "ON UPDATE {$this->data['UPDATE_RULE']}",
        ];
    }


}