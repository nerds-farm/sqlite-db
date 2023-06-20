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
    
    public static $nulled_query = 'SELECT 1 ;';

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
        add_action('sqlite-db/log', [$this, 'log'], 10, 2);
        add_filter('sqlite-db/query', [$this, 'fix_query']);
        add_filter('pre_query_sqlite_db', [$this, 'rewrite_query'], 10, 5);
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

    public function log($statement, $is_error = false) {
        if (WP_DEBUG && WP_DEBUG_LOG && defined('SQLITE_LOG') && SQLITE_LOG) {
            $log = FQDBDIR . SQLITE_LOG . '.sql.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $statement . PHP_EOL;
            // Write the contents to the file, 
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($log, $line, FILE_APPEND | LOCK_EX);
        }
        if (WP_DEBUG && WP_DEBUG_LOG && $is_error) {
            $debug = FQDBDIR . 'debug.sql.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $statement . PHP_EOL;
            // Write the contents to the file, 
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($debug, $line, FILE_APPEND | LOCK_EX);
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
        
        $query = $this->last_query;
        do_action('sqlite-db/log', $query, true);
            
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
            $query = htmlspecialchars($query, ENT_QUOTES);
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

        $sqlite_includes = SQLITE_DB_PATH . 'wp-includes' . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR;
        require_once $sqlite_includes . 'class-wp-sqlite-lexer.php';
        require_once $sqlite_includes . 'class-wp-sqlite-query-rewriter.php';
        require_once $sqlite_includes . 'class-wp-sqlite-translator.php';
        require_once $sqlite_includes . 'class-wp-sqlite-token.php';
        require_once $sqlite_includes . 'class-wp-sqlite-pdo-user-defined-functions.php';
        require_once $sqlite_includes . 'class-wp-sqlite-db.php';
        require_once $sqlite_includes . 'install-functions.php';
        $this->dbh = new \WP_SQLite_Translator($pdo);
        
        $this->last_error = $this->dbh->get_error_message();
        if (!empty($this->last_error)) {
            return false;
        }

        if (!$this->dbh) {
            wp_load_translations_early(); //probably there's no translations
            $this->bail(sprintf(__("<h1>Error establlishing a database connection</h1><p>We have been unable to connect to the specified database. <br />The error message received was %s"), $this->dbh->errorInfo()));
            return;
        }
        
        $GLOBALS['@pdo'] = $this->dbh->get_pdo();
        
        // Create compatibility functions for use within that database connection.
        $vendor = SQLITE_DB_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (file_exists($vendor)) {
            require_once $vendor;
            $pdo = $this->dbh->get_pdo();
            $this->dbh->set_pdo( \Vectorface\MySQLite\MySQLite::createFunctions($pdo) );
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

    public function fix_query($statement) {
        $original = $statement;
        $statement = trim($statement);
        //do_action('sqlite-db/log', $original);
        
        // CREATE
        $statement = $this->create_unique_index($statement);
        
        // ALTER TABLE
        $statement = $this->add_column($statement);
        $statement = $this->strip_comment($statement);
        $statement = $this->strip_after($statement);
        
        // UPDATE
        $statement = $this->update_option_null($statement);
        $statement = $this->update_order_by($statement);
        $statement = $this->update_limit($statement);
        
        // CHAR
        $statement = $this->char_length($statement);
        // DATE
        $statement = $this->select_date($statement);
        $statement = $this->select_date($statement, 'MONTH');
        $statement = $this->select_date($statement, 'DAY');
        
        if ($statement != $original) {
            do_action('sqlite-db/log', $original);
        }
        return $statement;
    }
    
    public function rewrite_query($pre, $translator, $statement, $mode, $fetch_mode_args) {        
        
        if ($pre) {
            return $pre;
        }
        
        $original = $statement;
        $tmp = null;
        
        // PRAGMA
        //https://www.sqlite.org/pragma.html
        $tmp = $tmp ? $tmp : $this->pragma($statement);
        
        // SHOW
        $tmp = $tmp ? $tmp : $this->show($statement);
        $tmp = $tmp ? $tmp : $this->describe($statement);
        $tmp = $tmp ? $tmp : $this->show_variables($statement);
        
        // INSERT
        //$tmp = $tmp ? $tmp : $this->on_duplicate($statement);
        
        // DELETE
        $tmp = $tmp ? $tmp : $this->delete_multiple($statement);
        
        // ALTER TABLE
        $tmp = $tmp ? $tmp : $this->on_update($statement);
        $tmp = $tmp ? $tmp : $this->add_index($statement);
        $tmp = $tmp ? $tmp : $this->empty_alter($statement);
        
        if (isset($_GET['action']) && $_GET['action'] == 'translate') return $tmp;
        
        do_action('sqlite-db/log', $original);
        if ($tmp) {
            
            $statement = $tmp;
            do_action('sqlite-db/log', $statement);
  
            try {
                    // Perform all the queries in a nested transaction.
                    $this->dbh->begin_transaction();

                    do {
                            $error = null;
                            try {
                                    $this->dbh->execute_mysql_query(
                                            $statement
                                    );
                            } catch ( PDOException $error ) {
                                    //if ( $error->getCode() !== self::SQLITE_BUSY ) {
                                            throw $error;
                                    //}
                            }
                    } while ( $error );

                    // Commit the nested transaction.
                    $this->dbh->commit();
                    return $this->dbh->get_return_value();
            } catch ( Exception $err ) {
                    // Rollback the nested transaction.
                    $this->dbh->rollback();
                    if ( defined( 'PDO_DEBUG' ) && PDO_DEBUG === true ) {
                            throw $err;
                    }
                    return $this->dbh->handle_error( $err );
            }
        }
        return $pre;
    }
    
    private function pragma($statement) {
        if (str_starts_with($statement, "PRAGMA ")) {
            return $statement;
        }
        return null;
    }
    
    private function char_length($statement) {
        // CHAR_LENGTH 
        return str_replace(' CHAR_LENGTH(', ' LENGTH(', $statement);
    }
    
    private function select_date($statement, $date = 'YEAR') {
        //SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM wp_posts  WHERE post_type = 'post' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC 
        $str = " ".$date."(";
        if (strpos($statement, $str) !== false) {
            // TODO,
            $pieces = explode($str, $statement);
            $tmp = '';
            foreach($pieces as $key => $piece) {
                if ($key) {
                    list($field, $more) = explode(')', $piece, 2);
                    switch($date) {
                        case 'DAY':
                            $time = '%d';
                            break;
                        case 'MONTH':
                            $time = '%m';
                            break;
                        case 'YEAR':
                        default:
                            $time = '%Y';
                    }
                    $tmp .= " strftime('".$time."', ".$field.")".$more;
                } else {
                    $tmp = $piece;
                }
            }
            $statement = $tmp;
        }
        return $statement;
    }
    
    private function delete_multiple($statement) {
        //DELETE a, b FROM wp_options a, wp_options b WHERE a.option_name LIKE '_transient_%' AND a.option_name NOT LIKE '_transient_timeout_%' AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
        if (str_starts_with($statement, "DELETE ")) {
            // TODO
            //$statement = "DELETE FROM wp_options WHERE option_name LIKE '_transient_%' AND option_name NOT LIKE '_transient_timeout_%'";
            //return $statement;
        }
        return null;
    }
    
    private function drop_primary_key($statement) {
        //ALTER TABLE wp_woocommerce_downloadable_product_permissions DROP PRIMARY KEY, ADD `permission_id` bigint(20) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;
        if (str_starts_with($statement, "ALTER TABLE ")) {
            $tmp = explode(' DROP PRIMARY KEY', $statement, 2); // drop column
            if (count($tmp) == 2) {
                $more = end($tmp);
                if (trim($more)) {
                    // strip drop, because is not supported
                    // https://www.sqlite.org/lang_altertable.html#alter_table_drop_column
                    $pre = reset($tmp);
                    if ($more[0] == ',') {
                        $more = substr($more, 1);
                    }
                    $statement = $pre.$more; // nulled query
                } else {
                    $statement = self::$nulled_query; // nulled query
                }
                $statement = str_replace(' ADD COLUMN COLUMN ', ' ADD COLUMN ', $statement); // check if is double
            }
        }
        return $statement;
    }
    
    private function create_unique_index($statement) {
        return str_replace('CREATE UNIQUE INDEX ', 'CREATE INDEX ', $statement);
    }
    
    private function add_column($statement) {
        if (str_starts_with($statement, "ALTER TABLE ")) {
            $statement = str_replace(' ADD `', ' ADD COLUMN `', $statement); // add column
            //$statement = str_replace(' ADD COLUMN COLUMN ', ' ADD COLUMN ', $statement); // check if is double
            //$statement = str_replace(' ADD COLUMN INDEX ', ' ADD INDEX ', $statement); // index?
            //$statement = str_replace(' ADD COLUMN UNIQUE INDEX ', ' ADD UNIQUE INDEX ', $statement); // index?
        }
        return $statement;
    }
    
    private function add_index($statement) {
        //ALTER TABLE `wp_e_submissions_actions_log` ADD INDEX `submission_id_index` (`submission_id`),ADD INDEX `action_name_index` (`action_name` (191)),ADD INDEX `status_index` (`status`),ADD INDEX `created_at_gmt_index` (`created_at_gmt`),ADD INDEX `updated_at_gmt_index` (`updated_at_gmt`),ADD INDEX `created_at_index` (`created_at`),ADD INDEX `updated_at_index` (`updated_at`);
        if (str_starts_with($statement, "ALTER TABLE ")) {
            $statement = str_replace('ADD UNIQUE INDEX ', 'ADD INDEX ', $statement);
            $statement = str_replace('ADD PRIMARY INDEX ', 'ADD INDEX ', $statement);
            $statement = str_replace('ADD UNIQUE KEY ', 'ADD INDEX ', $statement);
            $statement = str_replace('ADD PRIMARY KEY ', 'ADD INDEX ', $statement);
            $statement = str_replace('ADD KEY ', 'ADD INDEX ', $statement);
            $tmp = explode('ADD INDEX ', $statement);
            if (count($tmp) > 1) {
                $temp = explode(' ', array_shift($tmp));
                $table = $temp[2];
                //$table = str_replace('`', '', $table);
                $temp = '';
                foreach($tmp as $index) {
                    list($index_name, $more) = explode('(', $index, 2);
                    $more = str_replace('(', ')', $more); // (191)
                    list($index_field, $none) = explode(')', $more, 2);
                    $temp .= 'CREATE INDEX '.$index_name.' ON '.$table.' ('.$index_field.');';
                }
                return $temp;
            }
        }
        return null;
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function show($statement) {
        //  SHOW KEYS FROM `wp_yoast_indexable`
        // SHOW INDEX FROM wp_comments WHERE column_name = 'comment_type' and key_name = 'woo_idx_comment_type'
        // SHOW KEYS FROM wp_woocommerce_sessions WHERE Key_name = 'PRIMARY' AND Column_name = 'session_id'
        if (str_starts_with($statement, "SHOW KEYS ") || str_starts_with($statement, "SHOW INDEX ") || str_starts_with($statement, "SHOW INDEXES ")) {
            $tmp = explode(' ', $statement);
            $table = $tmp[3];
            $table = str_replace('`', '', $table);
            $table = str_replace(';', '', $table);
            $tmp = explode(' WHERE ', $statement, 2);
            $where = '';
            if (count($tmp) == 2) {
                $where = ' AND '.end($tmp);
                $where = str_replace("Key_name = 'PRIMARY'", 'pk <> 0', $where);
                $where = str_replace('Column_name', 'name', $where);
            }
            $sql = 'SELECT * FROM pragma_table_info("'.$table.'") WHERE pk <> 0'.$where;
            $indices = $this->dbh->query( $sql );
            $tmp = '';
            if (!empty($indices)) {
                foreach ($indices as $index) {
                    if ($tmp) $tmp .= ' UNION ';
                    $null = $index->notnull == '0' ? 'YES' : '';
                    $pk = intval($index->pk);
                    //$default = $field->dflt_value ? $field->dflt_value : 'NULL';
                    // cid, name, type, notnull, dflt_value, pk
                    // https://dev.mysql.com/doc/refman/8.0/en/show-index.html
                    $tmp .= "SELECT '".$table."' as 'Table', 0 as Non_unique, 'PRIMARY' as Key_name, ".$pk." as Seq_in_index, '".$index->name."' as Column_name, 'A' as Collation, ".$pk." as Cardinality, null as Sub_part, null as Packed, '".$null."' as 'Null', 'BTREE' as Index_type, '' as 'Comment', '' as Index_comment, 'YES' as Visible, null as Expression";
                }
                //$statement = $tmp;
                return $tmp;
            }
        }
        return null;
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function describe($statement) {
        //DESCRIBE wp_wc_webhooks;
        if (str_starts_with($statement, "DESCRIBE ") || str_starts_with($statement, "DESC ") || str_starts_with($statement, "EXPLAIN ")) {
            $tmp = explode(' ', $statement);
            $tmp = array_filter($tmp);
            $table = $tmp[1];
            $table = str_replace('`', '', $table);
            $table = str_replace(';', '', $table);
            $sql = 'SELECT * FROM pragma_table_info("'.$table.'")';
            $fields = $this->dbh->query( $sql );
            //var_dump($sql); var_dump($fields); die();
            $tmp = '';
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    //var_dump($field); die();
                    if ($tmp) $tmp .= ' UNION ';
                    $null = $field->notnull == '0' ? 'YES' : '';
                    $default = $field->dflt_value ? $field->dflt_value : 'NULL';
                    $pk = $field->pk > 0 ? 'PRI' : '';
                    // cid, name, type, notnull, dflt_value, pk
                    $tmp .= "SELECT '".$field->name."' as Field, '".$field->type."' as Type, '".$null."' as 'Null', '".$pk."' as Key, ".$default." as 'Default', '' as Extra";
                }
                //$statement = $tmp;
                return $tmp;
            }
            //var_dump($statement);
        }
        return null;
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function on_duplicate($statement) {
        // INSERT INTO `wp_options` (`option_name`, `option_value`, `autoload`) VALUES ('_transient_doing_cron', '1684255896.5588810443878173828125', 'yes') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)
        if (str_starts_with($statement, "INSERT INTO ")) {
            $tmp = explode(" ON DUPLICATE KEY UPDATE ", $statement, 2);
            //var_dump($statement); die();
            $keys = end($tmp);
            if (count($tmp) > 1) {
                $statement = reset($tmp);
                $temp = str_replace('(', '', $statement);
                $temp = str_replace(')', '', $temp);
                list($fields, $values) = explode(' VALUES ', $temp);
                
                $tmp = explode(' ', $fields, 4);
                $tmp = array_filter($tmp);
                $table = $tmp[2];
                $table = str_replace('`', '', $table);
                
                $fields = explode(',', end($tmp));
                $values = explode(',', $values);
                $values = array_filter($values);
                if (count($fields) == count($values)) {
                    foreach ($fields as $key => $field) {
                        $field = trim(str_replace('`', '', $field));
                        $values[$field] = $values[$key];
                    }
                }
                
                $tmp = explode('VALUES(', $keys);
                $pks = [];
                foreach($tmp as $key => $value) {
                    if ($key) {
                        list($field, $more) = explode(')', $value, 2);
                        $field = trim(str_replace('`', '', $field));
                        $pks[] = $field;
                    }
                }
                //var_dump($pks); var_dump($values); die();
                $delete = 'DELETE FROM '.$table.' WHERE ';
                $where = '';
                foreach ($pks as $key => $pk) {
                    if ($where) $where .= ' AND ';
                    if (!empty($values[$pk])) {
                       $where .= $pk.' = '.$values[$pk];
                    }
                }
                if ($where) {
                    $statement = $delete.$where.';'.$statement;   
                    //do_action('sqlite-db/log', $statement);
                    //var_dump($statement); die();
                    return $statement;
                }            
            }
        }
        return null;
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function on_update($statement) {
        // ALTER TABLE `wp_yoast_indexable` ADD `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP
        if (str_starts_with($statement, "ALTER TABLE ")) {
            $tmp = explode(" ON UPDATE ", $statement, 2);
            $value = end($tmp);
            if (count($tmp) > 1) {
                $statement = reset($tmp);
                list($pre, $more) = explode(' ADD ', $statement, 2);
                
                $tmp = explode(' ', $pre);
                $tmp = array_filter($tmp);
                $table = end($tmp);
                $table = str_replace('`', '', $table);
                
                $tmp = explode(' ', $more);
                $tmp = array_filter($tmp);
                $field = reset($tmp);
                $field = str_replace('`', '', $field);
                
                $pks = $this->get_pk($table);
                $where = '';
                foreach ($pks as $pk) {
                    if ($where) $where .= ' AND ';
                    $where .= $pk.' = old.'.$pk;
                }
                
                $trigger = "CREATE TRIGGER [Update".$field."] AFTER UPDATE ON ".$table." FOR EACH ROW BEGIN UPDATE ".$table." SET ".$field." = ".$value." WHERE ".$where."; END";
                $statement .= ';'.$trigger;
                //do_action('sqlite-db/log', $statement);
                //var_dump($statement); die();
                return $statement;
            }
        }
        return null;
    }
    
    // Returns an array of columns by which rows can be uniquely adressed.
    // For tables with a rowid column, this is always array('rowid')
    // for tables without rowid, this is an array of the primary key columns. 
    public function get_pk($table) {
        $sql = 'SELECT l.name FROM pragma_table_info("'.$table.'") as l WHERE l.pk <> 0;';
        $indices = $this->dbh->query( $sql, \PDO::FETCH_ASSOC );
        //var_dump($indices); die();
        //$indices = $query->fetchAll();
        //$indices = $this->dbh->get_keys( $table, true);
        return reset($indices);
    }
    
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function show_variables($statement) {
        // SHOW VARIABLES WHERE `Variable_name` IN ( 'version_comment', 'innodb_version' )
        // version_comment 	MySQL Community Server (GPL)
        if (str_starts_with($statement, "SHOW VARIABLES ")) {
            $tmp = explode("(", $statement, 2);
            if (count($tmp) > 1) {
                $statement = '';
                $tmp = explode(',', end($tmp));
                $vars = [];
                foreach ($tmp as $field) {
                    $field = str_replace("'", '', $field);
                    $field = str_replace('"', '', $field);
                    $field = str_replace(' ', '', $field);
                    $field = str_replace(')', '', $field);
                    if ($field) {
                        switch($field) {
                            case 'version_comment':
                                $vars[$field] = 'SQLite (PDO)';
                                break;
                            case 'innodb_version':
                                $db = Utils::get_sqlite_db();
                                $vars[$field] = $db->getSQLiteVersion();
                                break;
                            default:
                                $vars[$field] = '';
                        }
                    }
                }
                foreach ($vars as $akey => $avar) {
                    if ($statement) {
                        $statement .= ' UNION ';
                    }
                    $statement .= "SELECT '".$akey."' as Variable_name, '".$avar."' as Value";
                }
                //var_dump($statement); die();
                return $statement;
            }
        }
        return null;
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
                return self::$nulled_query; // nulled query
            }
        }
        return null;
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
    private function update_order_by($statement) {
        //UPDATE wp_posts SET post_password = 'cd3ecfbb4e0139860aaf', post_modified_gmt = '2023-05-03 08:19:55', post_modified = '2023-05-03 08:19:55' WHERE post_type = 'scheduled-action' AND post_status = 'pending' AND post_password = '' AND post_date_gmt <= '2023-05-03 08:19:55' ORDER BY menu_order ASC, post_date_gmt ASC, ID ASC LIMIT 25
        if (str_starts_with($statement, 'UPDATE ')) {
            $tmp = explode(" ORDER BY ", $statement, 2);
            if (count($tmp) == 2) {
                $pre = reset($tmp);
                $more = end($tmp);
                $more = str_replace(' DESC', ' ASC', $more);
                $tmp = explode(' ASC', $more);
                $statement = $pre.end($tmp);
            }
        }
        return $statement;
    }
    /**
     * Method to strip column after
     *
     * @access private
     */
    private function update_limit($statement) {
        //UPDATE wp_posts SET post_password = 'cd3ecfbb4e0139860aaf', post_modified_gmt = '2023-05-03 08:19:55', post_modified = '2023-05-03 08:19:55' WHERE post_type = 'scheduled-action' AND post_status = 'pending' AND post_password = '' AND post_date_gmt <= '2023-05-03 08:19:55' ORDER BY menu_order ASC, post_date_gmt ASC, ID ASC LIMIT 25
        if (str_starts_with($statement, 'UPDATE ')) {
            $tmp = explode(" LIMIT ", $statement, 2);
            if (count($tmp) == 2) {
                if (intval(end($tmp))) {
                    $pre = reset($tmp);
                    $statement = $pre;
                }
            }
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
