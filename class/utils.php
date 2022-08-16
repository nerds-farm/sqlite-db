<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace WP_SQLite_DB;

/**
 * Description of utils
 *
 * @author Fra
 */
class Utils {
    
    /**
     * Returns server type for current connection
     *
     * Known types are: MariaDB, PerconaDB and MySQL (default)
     *
     * @return string
     */
    public static function getServerType()
    {
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
}
