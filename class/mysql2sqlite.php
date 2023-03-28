<?php

class Mysql2Sqlite {

    private $hostname = DB_HOST;
    private $username = DB_USER;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $sqlitedb = DB_NAME . ".sqlite";
    private $useUTF8 = true;
    
    public function __construct($args = []) {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    public function run() {
        // only for OS X users that use MAMP, check if there is a mysql socket to use
        $socket = file_exists("/Applications/MAMP/tmp/mysql/mysql.sock") ? "/Applications/MAMP/tmp/mysql/mysql.sock" : ini_get("mysqli.default_socket");

        // open mySql connection
        try {
            $options = $this->useUTF8 ? array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8') : array();
            $mysqli = new PDO("mysql:host=" . $this->hostname . ";dbname=" . $this->database . ";unix_socket=" . $socket, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->showMessageAndDie($e->getMessage());
        }

        // open Sqlite connection
        try {
            @unlink($this->sqlitedb);
            $sqlite = new PDO("sqlite:" . $this->sqlitedb);
        } catch (PDOException $e) {
            $this->showMessageAndDie($e->getMessage());
        }

        $this->showMessage("Start converting mysql:" . $this->database . " to sqlite:" . $this->sqlitedb);
        foreach ($mysqli->query("SHOW TABLES;") as $row) {
            $tableName = $row[0];
            $this->showMessage("Convert table: " . $tableName);
            $this->converTable($mysqli, $sqlite, $tableName);
        }
        $this->showMessage("Done!");
    }

    function converTable($mysqli, $sqlite, $tableName) {
        $createFields = array();
        $pkFields = array();
        $indexFields = array();
        $tableFields = array();

        foreach ($mysqli->query("SHOW COLUMNS FROM " . $tableName) as $row) {
            $tableFields[] = $row["Field"];
            $fieldType = "TEXT";
            if (stripos($row["Type"], "int(") !== false) {
                $fieldType = "INTEGER";
            } elseif (stripos($row["Type"], "datetime") !== false) {
                $fieldType = "DATETIME";
            } elseif (stripos($row["Type"], "date") !== false) {
                $fieldType = "DATE";
            }

            if ($row["Key"] == "PRI") {
                //$fieldType = "INTEGER";	
                $pkFields[] = $row["Field"];
            } else if ($row["Key"] == "MUL") {
                $indexFields[] = "CREATE INDEX `" . $row["Field"] . "_".$tableName . "_index` ON " . $tableName . "(`" . $row["Field"] . "`)";
            }
            $createFields[] = "`".$row["Field"]."`" . " " . $fieldType;
        }

        if (count($pkFields)) {
            array_push($createFields, "PRIMARY KEY (`" . implode("`,`", $pkFields) . "`)");
        }

        // create the table
        $create = "CREATE TABLE " . $tableName . " (" . implode(",", $createFields) . ")";
        $sqlite->exec($create);

        // insert statement
        $insertSqlPart = str_repeat("?,", count($tableFields));
        $insertSqlPart = substr($insertSqlPart, 0, -1);
        $insertSql = "INSERT INTO " . $tableName . "(`" . implode("`,`", $tableFields) . "`) VALUES ( " . $insertSqlPart . " ) ";
        $sth = $sqlite->prepare($insertSql);

        // get the number of records in the table
        $sthCount = $mysqli->query("SELECT count(*) FROM " . $tableName);
        $row = $sthCount->fetch();
        $numRows = $row[0];
        $sthCount->closeCursor();

        // read and convert all records
        $pageLength = 100000;
        $currentPage = 0;
        $i = 0;
        while (true) {
            $sqlite->beginTransaction();
            foreach ($mysqli->query("SELECT * FROM " . $tableName . " LIMIT " . $currentPage . "," . $pageLength) as $row) {
                $params = array();
                foreach ($tableFields as $v) {
                    $params[] = $row[$v];
                }

                $r = $sth->execute($params);
                if (!$r) {
                    // error
                    $this->showMessageAndDie(print_r($sqlite->errorInfo(), true));
                }

                $i++;
            }
            $sqlite->commit();

            if ($i < $numRows) {
                echo ".";
                $currentPage += $pageLength;
            } else {
                break;
            }
        }

        $this->showMessage("  imported: " . $i . " rows");

        // create index
        if (count($indexFields)) {
            $this->showMessage("  create index: " . implode(";", $indexFields));
            $sqlite->exec(implode(";", $indexFields));
        }
    }

    function showMessage($message) {
        //echo $message."\n";
    }

    function showMessageAndDie($message) {
        die($message . "\n\n");
    }

}

?>
