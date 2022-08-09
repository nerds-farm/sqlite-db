<?php

namespace Pilulka\Mysql2Sqlite\Tests;

use Pilulka\Mysql2Sqlite\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testSchemaCreation()
    {
        $converter = $this->converter();
        $sqlite = new \PDO('sqlite::memory:');
        $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->assertEquals(
            0,
            $sqlite->exec($converter->schema()),
            "Schema creation should not affect any rows of database."
        );
    }

    /**
     * @return Converter
     */
    private function converter()
    {
        $reflection = new \ReflectionClass(Converter::class);
        return $reflection->newInstanceArgs(
            include __DIR__ . "/config.php"
        );
    }

}
