<?php

namespace Pilulka\Mysql2Sqlite;

class Converter
{
    /** @var string */
    private $hostname;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var string */
    private $database;
    /** @var \PDO */
    private $pdo;
    /** @var DataRepository */
    private $repository;

    /**
     * Converter constructor.
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     */
    public function __construct($hostname, $username, $password, $database)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }


    public function schema()
    {
        $schema = "PRAGMA foreign_keys = OFF;\n\n";
        foreach ($this->repository()->tables() as $tableData) {
            $table = new Model\Table(
                $tableData,
                $this->repository()->columns($tableData['TABLE_NAME']),
                $this->repository()->indexes($tableData['TABLE_NAME']),
                $this->repository()->foreigns($tableData['TABLE_NAME'])
            );
            $schema .= "{$table->getSchema()}\n";
        }
        $schema .= "PRAGMA foreign_keys = ON;\n";
        return $schema;
    }


    /**
     * @return DataRepository
     */
    public function repository()
    {
        if(!isset($this->repository)) {
            $this->repository = new DataRepository(
                $this->pdo(),
                $this->database
            );
        }
        return $this->repository;
    }

    /**
     * @return \PDO
     */
    public function pdo()
    {
        if(!isset($this->pdo)) {
            $this->pdo = new \PDO(
                "mysql:host={$this->hostname};dbname=information_schema",
                $this->username,
                $this->password
            );
        }
        return $this->pdo;
    }

}
