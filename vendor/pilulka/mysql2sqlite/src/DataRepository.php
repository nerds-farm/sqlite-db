<?php

namespace Pilulka\Mysql2Sqlite;

class DataRepository
{

    /** @var \PDO */
    private $pdo;
    /** @var string */
    private $database;

    /**
     * DataRepository constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo, $database)
    {
        $this->pdo = $pdo;
        $this->database = $database;
    }

    public function columns($table)
    {
        $sql = <<<SQL
SELECT *
FROM COLUMNS
WHERE 
  TABLE_SCHEMA = ?
  AND TABLE_NAME = ?;        
SQL;
        return $this->fetchAll($sql, [$this->database, $table]);
    }

    public function foreigns($table)
    {
        $sql = <<<SQL
SELECT
  kcu.*,
  rc.UPDATE_RULE,
  rc.DELETE_RULE
FROM
  KEY_COLUMN_USAGE kcu
  JOIN REFERENTIAL_CONSTRAINTS rc
    ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND
       kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
WHERE
  kcu.CONSTRAINT_SCHEMA = ?
  AND kcu.TABLE_NAME = ?        
  AND kcu.REFERENCED_COLUMN_NAME IS NOT NULL;
SQL;
        return $this->fetchAll($sql, [$this->database, $table]);
    }

    public function indexes($table)
    {
        $sql = <<<SQL
SELECT *
FROM STATISTICS
WHERE 
  TABLE_SCHEMA = ?
  AND TABLE_NAME = ?;
SQL;
        return $this->fetchAll($sql, [$this->database, $table]);
    }

    public function tables()
    {
        $sql = <<<SQL
SELECT *
FROM TABLES
WHERE 
  TABLE_SCHEMA = ?;
SQL;
        return $this->fetchAll($sql, [$this->database]);
    }

    /**
     * @param $sql
     * @param $input
     * @return array
     */
    private function fetchAll($sql, $input)
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($input);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    private function pdo()
    {
        return $this->pdo;
    }

}