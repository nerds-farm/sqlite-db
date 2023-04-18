<?php

namespace SQLiteDB;

/**
 * This file defines PDODB class, which inherits wpdb class and replaces it
 * global $wpdb variable.
 *
 * @package SQLite Integration
 * @author Kojima Toshiyasu
 *
 */
if (!defined('ABSPATH')) {
    echo 'Thank you, but you are not allowed to accesss this file.';
    die();
}
//require_once SQLITE_DB_PATH . 'install.php';

if (!defined('SAVEQUERIES')) {
    define('SAVEQUERIES', false);
}
if (!defined('PDO_DEBUG')) {
    define('PDO_DEBUG', false);
}

/**
 * This class extends wpdb and replaces it.
 *
 * It also rewrites some methods that use mysql specific functions.
 *
 */
class PDODB extends \wpdb {

    /**
     *
     * @var reference to the object of PDOEngine class.
     * @access protected
     */
    protected $dbh = null;

    /**
     * Constructor
     *
     * This overrides wpdb::__construct() which has database server, username and
     * password as arguments. This class doesn't use them.
     *
     * @see wpdb::__construct()
     */
    public function __construct() {
        register_shutdown_function(array($this, '__destruct'));

        if (WP_DEBUG) {
            $this->show_errors();
        }

        $this->init_charset();

        $this->db_connect();
        add_action('sqlite-db/log', [$this, 'log']);
        add_filter('sqlite-db/query', [$this, 'rewrite_query']);
        add_filter('option_gmt_offset', function ($value, $option) {
            return intval($value);
        }, 10, 2);
    }

    /**
     * Desctructor
     *
     * This overrides wpdb::__destruct(), but does nothing but return true.
     *
     * @see wpdb::__destruct()
     */
    public function __destruct() {
        return true;
    }

    public function log($statement) {
        if (WP_DEBUG && WP_DEBUG_LOG && defined('SQLITE_LOG') && SQLITE_LOG) {
            $log = FQDBDIR . 'sql.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $statement . PHP_EOL;
            // Write the contents to the file, 
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($log, $line, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Method to set character set for the database.
     *
     * This overrides wpdb::set_charset(), only to dummy out the MySQL function.
     *
     * @see wpdb::set_charset()
     */
    public function set_charset($dbh, $charset = null, $collate = null) {
        if (!isset($charset))
            $charset = $this->charset;
        if (!isset($collate))
            $collate = $this->collate;
    }

    /**
     * Method to dummy out wpdb::set_sql_mode()
     *
     * @see wpdb::set_sql_mode()
     */
    public function set_sql_mode($modes = array()) {
        unset($modes);
        return;
    }

    /**
     * Method to select the database connection.
     *
     * This overrides wpdb::select(), only to dummy out the MySQL function.
     *
     * @see wpdb::select()
     */
    public function select($db, $dbh = null) {
        if (is_null($dbh))
            $dbh = $this->dbh;
        $this->ready = true;
        return;
    }

    /**
     * Method to dummy out wpdb::_weak_escape()
     *
     */
    function _weak_escape($string) {
        return empty($string) ? '' : addslashes($string);
    }

    /**
     * Method to escape characters.
     *
     * This overrides wpdb::_real_escape() to avoid using mysql_real_escape_string().
     *
     * @see wpdb::_real_escape()
     */
    function _real_escape($string) {
        return empty($string) ? '' : addslashes($string);
    }

    /**
     * Method to dummy out wpdb::esc_like() function.
     *
     * WordPress 4.0.0 introduced esc_like() function that adds backslashes to %,
     * underscore and backslash, which is not interpreted as escape character
     * by SQLite. So we override it and dummy out this function.
     *
     * @see wpdb::esc_like()
     */
    public function esc_like($text) {
        return $text;
    }

    /**
     * Method to put out the error message.
     *
     * This overrides wpdb::print_error(), for we can't use the parent class method.
     *
     * @see wpdb::print_error()
     */
    public function print_error($str = '') {
        global $EZSQL_ERROR;

        if (!$str) {
            $err = $this->dbh->get_error_message() ? $this->dbh->get_error_message() : '';
            if (!empty($err))
                $str = $err[2];
            else
                $str = '';
        }
        $EZSQL_ERROR[] = array('query' => $this->last_query, 'error_str' => $str);

        if ($this->suppress_errors)
            return false;

        wp_load_translations_early();

        if ($caller = $this->get_caller())
            $error_str = sprintf(__('WordPress database error %1$s for query %2$s made by %3$s'), $str, $this->last_query, $caller);
        else
            $error_str = sprintf(__('WordPress database error %1$s for query %2$s'), $str, $this->last_query);

        error_log($error_str);

        if (!$this->show_errors)
            return false;

        if (is_multisite()) {
            $msg = "WordPress database error: [$str]\n{$this->last_query}\n";
            if (defined('ERRORLOGFILE'))
                error_log($msg, 3, ERRORLOGFILE);
            if (defined('DIEONDBERROR'))
                wp_die($msg);
        } else {
            //$str = htmlspecialchars($str, ENT_NOQUOTES);
            //$str = str_replace('&lt;', '<', $str);
            //$str = str_replace('&gt;', '>', $str);
            $query = htmlspecialchars($this->last_query, ENT_QUOTES);

            echo '<div id="error" style="padding: 15px; background-color: #ffffdd;">
			<p class="wpdberror"><strong>WordPress database error:</strong> [' . $str . ']<br />
			<code>' . $query . '</code></p>
			</div>';
        }
    }

    /**
     * Method to flush cached data.
     *
     * This overrides wpdb::flush(). This is not necessarily overridden, because
     * $result will never be resource.
     *
     * @see wpdb::flush
     */
    public function flush() {
        $this->last_result = array();
        $this->col_info = null;
        $this->last_query = null;
        $this->rows_affected = $this->num_rows = 0;
        $this->last_error = '';
        $this->result = null;
    }

    /**
     * Method to do the database connection.
     *
     * This overrides wpdb::db_connect() to avoid using MySQL function.
     *
     * @see wpdb::db_connect()
     */
    public function db_connect($allow_bail = true) {

        if ($this->dbh) {
            return;
        }
        $this->init_charset();

        $pdo = null;
        if (isset($GLOBALS['@pdo'])) {
            $pdo = $GLOBALS['@pdo'];
        }

        $includes = SQLITE_DB_PATH . 'wp-includes' . DIRECTORY_SEPARATOR;
        $performance = true;

        if ($performance) {
            $sqlite_includes = $includes . 'sqlite' . DIRECTORY_SEPARATOR;
            require_once $sqlite_includes . 'class-wp-sqlite-lexer.php';
            require_once $sqlite_includes . 'class-wp-sqlite-query-rewriter.php';
            require_once $sqlite_includes . 'class-wp-sqlite-translator.php';
            require_once $sqlite_includes . 'class-wp-sqlite-token.php';
            require_once $sqlite_includes . 'class-wp-sqlite-pdo-user-defined-functions.php';
            require_once $sqlite_includes . 'class-wp-sqlite-db.php';
            require_once $sqlite_includes . 'install-functions.php';
            $this->dbh = new \WP_SQLite_Translator($pdo);
        } else {
            require_once $includes . 'integration' . DIRECTORY_SEPARATOR . 'pdoengine.php';
            $this->dbh = new PDOEngine();
        }

        $this->last_error = $this->dbh->get_error_message();
        if (!empty($this->last_error)) {
            return false;
        }

        if (!$this->dbh) {
            wp_load_translations_early(); //probably there's no translations
            $this->bail(sprintf(__("<h1>Error establlishing a database connection</h1><p>We have been unable to connect to the specified database. <br />The error message received was %s"), $this->dbh->errorInfo()));
            return;
        }
        /*
          // Create compatibility functions for use within that database connection.
          $vendor = SQLITE_DB_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
          if (file_exists($vendor)) {
          require_once $vendor;
          //$this->dbh = \Vectorface\MySQLite\MySQLite::createFunctions($this->dbh);
          }
         */
        if ($performance) {
            $GLOBALS['@pdo'] = $this->dbh->get_pdo();
        }
        $this->has_connected = true;
        $this->ready = true;
    }

    /**
     * Method to dummy out wpdb::check_connection()
     *
     */
    public function check_connection($allow_bail = true) {
        return true;
    }

    /**
     * Method to execute the query.
     *
     * This overrides wpdb::query(). In fact, this method does all the database
     * access jobs.
     *
     * @see wpdb::query()
     */
    public function query($query) {
        if (!$this->ready)
            return false;

        $query = apply_filters('query', $query);

        $return_val = 0;
        $this->flush();

        $this->func_call = "\$db->query(\"$query\")";

        $this->last_query = $query;

        if (defined('SAVEQUERIES') && SAVEQUERIES)
            $this->timer_start();

        $this->result = $this->dbh->query($query);
        $this->num_queries++;

        if (defined('SAVEQUERIES') && SAVEQUERIES)
            $this->queries[] = array($query, $this->timer_stop(), $this->get_caller());

        if ($this->last_error = $this->dbh->get_error_message()) {
            if (defined('WP_INSTALLING') && WP_INSTALLING) {
                //$this->suppress_errors();
            } else {
                $this->print_error($this->last_error);
                return false;
            }
        }

        if (preg_match('/^\\s*(create|alter|truncate|drop|optimize)\\s*/i', $query)) {
            //$return_val = $this->result;
            $return_val = $this->dbh->get_return_value();
        } elseif (preg_match('/^\\s*(insert|delete|update|replace)\s/i', $query)) {
            $this->rows_affected = $this->dbh->get_affected_rows();
            if (preg_match('/^\s*(insert|replace)\s/i', $query)) {
                $this->insert_id = $this->dbh->get_insert_id();
            }
            $return_val = $this->rows_affected;
        } else {
            $this->last_result = $this->dbh->get_query_results();
            $this->num_rows = $this->dbh->get_num_rows();
            $return_val = $this->num_rows;
        }
        return $return_val;
    }

    /**
     * Method for future use?
     *
     * WordPress 3.9 separated the method to execute real query from query() function.
     * This is for the restoration from the case that nothing returns from database.
     * But this is necessary because we aleady did error manipulations in
     * pdoengine.class.php. So we don't use this function.
     *
     * @access private
     */
    private function _do_query($query) {
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            $this->timer_start();
        }
        $this->result = $this->dbh->query($query);
        $this->num_queries++;
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            $this->queries[] = array($query, $this->timer_stop(), $this->get_caller());
        }
    }

    /**
     * Method to set the class variable $col_info.
     *
     * This overrides wpdb::load_col_info(), which uses a mysql function.
     *
     * @see wpdb::load_col_info()
     * @access protected
     */
    protected function load_col_info() {
        if ($this->col_info)
            return;
        $this->col_info = $this->dbh->get_columns();
    }

    /**
     * Method to return what the database can do.
     *
     * This overrides wpdb::has_cap() to avoid using MySQL functions.
     * SQLite supports subqueries, but not support collation, group_concat and set_charset.
     *
     * @see wpdb::has_cap()
     */
    public function has_cap($db_cap) {
        switch (strtolower($db_cap)) {
            case 'collation':
            case 'group_concat':
            case 'set_charset':
                return false;
            case 'subqueries':
                return true;
            default:
                return false;
        }
    }

    /**
     * Method to return database version number.
     *
     * This overrides wpdb::db_version() to avoid using MySQL function.
     * It returns mysql version number, but it means nothing for SQLite.
     * So it return the newest mysql version.
     *
     * @see wpdb::db_version()
     */
    public function db_version() {
        //global $required_mysql_version;
        //return $required_mysql_version;
        return '8.0';
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from 
     * $params are are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    public static function interpolate_query($query, $params) {
        $keys = array();
        $values = $params;
        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_string($value))
                $values[$key] = "'" . $value . "'";
            if (is_array($value))
                $values[$key] = "'" . implode("','", $value) . "'";
            if (is_null($value))
                $values[$key] = 'NULL';
        }
        $query = preg_replace($keys, $values, $query);
        return $query;
    }

    public function rewrite_query($statement) {
        $original = $statement;
        $statement = trim($statement);
        //do_action('sqlite-db/log', $original);

        // PRAGMA
        
        $statement = $this->empty_alter($statement);
        $statement = $this->strip_comment($statement);
        
        $statement = $this->strip_after($statement);
        $statement = $this->update_option_null($statement);
        if ($statement != $original) {
            do_action('sqlite-db/log', $original);
        }
        do_action('sqlite-db/log', $statement);
        return $statement;
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function empty_alter($statement) {
        // ALTER TABLE `wp_e_submissions` ;
        // ALTER TABLE `wp_e_submissions_values` ;
        // ALTER TABLE `wp_e_submissions_actions_log` ;
        if (str_starts_with($statement, "ALTER TABLE ")) {
            $tmp = explode("`", $statement);
            if (count($tmp) < 3 || trim($tmp[2]) == ';') {
                $statement = 'SELECT 1 ;'; // nulled query
            }
        }
        return $statement;
    }

    /**
     * Method to strip column after
     *
     * @access private
     */
    private function update_option_null($statement) {
        global $wpdb;
        $update_option_null = "UPDATE `" . $wpdb->options . "` SET `option_value` = NULL WHERE `option_name` = ";
        if (str_starts_with($statement, $update_option_null)) {
            $statement = str_replace($update_option_null, "DELETE FROM `" . $wpdb->options . "` WHERE `option_name` = ", $statement);
        }
        return $statement;
    }

    /**
     * Method to strip column after
     *
     * @access private
     */
    private function strip_after($statement) {
        $query = $statement;
        if (str_starts_with($statement, 'ALTER TABLE ')) {
            if (stripos($statement, 'ADD COLUMN') !== false) {
                if (stripos($statement, ' AFTER ') !== false) {
                    // remove the ' AFTER ' not supported
                    list($statement, $after) = explode(' AFTER ', $statement, 2);
                }
            }
            if (stripos($statement, ' RENAME COLUMN ') !== false) {
                // retrieve table structure
                // add temp table with renamed col
                // copy content
                // delete old table
                // rename temp table
            }
        }
        return $statement;
    }

    /**
     * Method to strip column comment
     *
     * @access private
     */
    private function strip_comment($statement) {
        $query = $statement;
        if (str_starts_with($statement, 'CREATE TABLE ') || str_starts_with($statement, 'ALTER TABLE ')) {
            foreach (["'", '"'] as $quote) {
                foreach (['comment', 'COMMENT'] as $cmn) {
                    $tmp = explode(" " . $cmn . " " . $quote, $query);
                    if (count($tmp) > 1) {
                        $query = '';
                        foreach ($tmp as $key => $piece) {
                            if ($key) {
                                list($comment, $piece) = explode($quote, $piece, 2);
                            }
                            $query .= $piece;
                        }
                    }
                }
            }
        }
        return $query;
    }

}

/*
 * Initialize $wpdb with PDODB class
 */
if (!isset($wpdb)) {
    global $wpdb;
    $wpdb = new \SQLiteDB\PDODB();
}
?>
