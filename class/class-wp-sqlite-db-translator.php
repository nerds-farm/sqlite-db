<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace SQLiteDB;

/**
 * Description of WP_SqliteDB_Translator
 *
 * @author Fra
 */
class WP_SQLiteDB_Translator extends \WP_SQLite_Translator {

    /**
     * Method to execute query().
     *
     * Divide the query types into seven different ones. That is to say:
     *
     * 1. SELECT SQL_CALC_FOUND_ROWS
     * 2. INSERT
     * 3. CREATE TABLE(INDEX)
     * 4. ALTER TABLE
     * 5. SHOW VARIABLES
     * 6. DROP INDEX
     * 7. THE OTHERS
     *
     * #1 is just a tricky play. See the private function handle_sql_count() in query.class.php.
     * From #2 through #5 call different functions respectively.
     * #6 call the ALTER TABLE query.
     * #7 is a normal process: sequentially call prepare_query() and execute_query().
     *
     * #1 process has been changed since version 1.5.1.
     *
     * @param string $statement          Full SQL statement string.
     * @param int    $mode               Not used.
     * @param array  ...$fetch_mode_args Not used.
     *
     * @see PDO::query()
     *
     * @throws Exception    If the query could not run.
     * @throws PDOException If the translated query could not run.
     *
     * @return mixed according to the query type
     */
    #[\ReturnTypeWillChange]
    public function query($statement, $mode = \PDO::FETCH_OBJ, ...$fetch_mode_args) { // phpcs:ignore WordPress.DB.RestrictedClasses
        //$this->pdo_fetch_mode = $mode;
        /*
         * Fix the query before execute it
         */
        $statement = apply_filters('sqlite-db/query', $statement);
        
	parent::query($statement, $mode, $fetch_mode_args);
    }

    /**
     * Set the PDO object.
     *
     * @return PDO
     */
    public function set_pdo($pdo) {
        return $this->pdo = $pdo;
    }

    /**
     * Executes a MySQL query in SQLite.
     *
     * @param string $query The query.
     *
     * @throws Exception If the query is not supported.

     */
    public function execute_mysql_query($query) {
        $tokens = ( new \WP_SQLite_Lexer($query) )->tokens;
        $this->rewriter = new \WP_SQLite_Query_Rewriter($tokens);
        $query_type = $this->rewriter->peek()->value;

        switch ($query_type) {
            case 'PRAGMA':
                $stmt = $this->execute_sqlite_query($query);
                $this->results = $stmt->fetchAll($this->pdo_fetch_mode);
                break;

            default:
                $fallback = new \WP_SQLite_Translator();
                $fallback->query($query);
        }
    }

    /**
     * Translates a CREATE query.
     *
     * @throws Exception If the query is an unknown create type.

     */
    private function execute_create() {
        $this->rewriter->consume();
        $what = $this->rewriter->consume()->token;

        /**
         * Technically it is possible to support temporary tables as follows:
         *    ATTACH '' AS 'tempschema';
         *    CREATE TABLE tempschema.<name>(...)...;
         * However, for now, let's just ignore the TEMPORARY keyword.
         */
        if ('TEMPORARY' === $what) {
            $this->rewriter->drop_last();
            $what = $this->rewriter->consume()->token;
        }

        switch ($what) {
            case 'TABLE':
                $this->execute_create_table();
                break;

            case 'PROCEDURE':
            case 'DATABASE':
                $this->results = true;
                break;

            case 'INDEX':
                $this->execute_create_index();
                break;

            default:
                throw new \Exception('Unknown create type: ' . $what);
        }
    }

    private function execute_create_index() {
        //var_dump($this); die();
        if ($this->mysql_query) {
            $this->execute_sqlite_query($this->mysql_query);
            $this->results = $this->last_exec_returned;
            $this->return_value = $this->results;
        }
    }
}
