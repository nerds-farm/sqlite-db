<?php

namespace Pilulka\Mysql2Sqlite\Model;

abstract class Base
{

    protected $data;

    /**
     * Column constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return implode(
            " ",
            array_filter(
                $this->parts(),
                function ($value) {
                    return !!$value;
                }
            )
        );
    }

    abstract public function parts();

}
