<?php

namespace Pilulka\Mysql2Sqlite\Model;

class Index extends Base
{

    public function parts()
    {
        if($this->data['INDEX_NAME'] == 'PRIMARY') return [];
        return [
            'CREATE INDEX',
            "{$this->data['TABLE_NAME']}_{$this->data['COLUMN_NAME']}",
            'ON',
            $this->data['TABLE_NAME'],
            "({$this->data['COLUMN_NAME']});",
        ];
    }

}
