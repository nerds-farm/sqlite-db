<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace SQLite_DB;

/**
 * Description of utils
 *
 * @author Fra
 */
class Utils {
    
    public static function get_sqlite_db() {
        $args = [
            'path' => DB_SQLITE,
            'name' => DB_NAME,
        ];
        define("FORCETYPE", false);
        $path = SQLITE_DB_PATH . 'utilities' . DIRECTORY_SEPARATOR . 'phpliteadmin' . DIRECTORY_SEPARATOR;
        include_once $path . 'database.php';
        $db = new \PhpLiteAdmin\Database($args);
        return $db;
    }

    /**
     * Returns server type for current connection
     *
     * Known types are: MariaDB, PerconaDB and MySQL (default)
     *
     * @return string
     */
    public static function getServerType() {
        global $wpdb;

        $version = $this->fetchSingleRow(
                'SELECT @@version, @@version_comment',
                'ASSOC',
                self::CONNECT_USER
        );

        if (is_array($version)) {
            $this->versionString = $version['@@version'] ?? '';
            $this->versionInt = Utilities::versionToInt($this->versionString);
            $this->versionComment = $version['@@version_comment'] ?? '';
            if (stripos($this->versionString, 'mariadb') !== false) {
                return 'MariaDB';
            }
            if (stripos($this->versionComment, 'percona') !== false) {
                return 'Percona Server';
            }
        }

        return 'MySQL';
    }

    /**
     * Get MySQL version.
     *
     * Retrieve the MySQL version.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array {
     *    Report data.
     *
     *    @type string $value MySQL version.
     * }
     */
    public static function get_mysql_version() {
        global $wpdb;

        $db_server_version = $wpdb->get_results("SHOW VARIABLES WHERE `Variable_name` IN ( 'version_comment', 'innodb_version' )", OBJECT_K);

        $db_server_version_string = $db_server_version['version_comment']->Value . ' v';

        // On some hosts, `innodb_version` is empty, in PHP 8.1.
        if (isset($db_server_version['innodb_version'])) {
            $db_server_version_string .= $db_server_version['innodb_version']->Value;
        } else {
            $db_server_version_string .= $wpdb->get_var('SELECT VERSION() AS version');
        }

        return $db_server_version_string;
    }
    
    public static function get_sqlite_version() {
        $db = self::get_sqlite_db();
        return $db->getSQLiteVersion();
    }

}
