<?php
namespace SQLiteDB;
/**
 * This file defines DatabaseMaintenance class.
 *
 * When WordPress was upgraded from 3.5.x to 3.6, SQLite Integration couldn't manipulate
 * dbDelta() function of WordPress as expected. As a result, there are some tables whose
 * default values are missing.
 *
 * This file is for temporary use and will be removed or changed in the future release.
 *
 * @package SQLite Integration
 * @author Kojima Toshiyasu
 */

/**
 * This class provide the methods to check the table schemas and restore if necessary.
 *
 * Each method is a private function except the one to show admin page.
 *
 */
class SQLiteDbMaintenance {

    /**
     * Method to check the table schemas.
     *
     * If there are any broken tables, it returns the array of the table names to be fixed.
     * If not, it returns true.
     *
     * @return boolean|multitype:string
     * @access private
     */
    public static function sanity_check() {
        global $wpdb;
        $results_table = array();
        $columns_to_check = array(
            $wpdb->prefix . 'commentmeta' => array(
                'comment_id' => '\'0\'',
                'meta_key' => 'NULL'
            ),
            $wpdb->prefix . 'comments' => array(
                'comment_post_ID' => '\'0\'',
                'comment_author_email' => '\'\'',
                'comment_author_url' => '\'\'',
                'comment_author_IP' => '\'\'',
                'comment_date_gmt' => '\'0000-00-00 00:00:00\'',
                'comment_date' => '\'0000-00-00 00:00:00\'',
                'comment_karma' => '\'0\'',
                'comment_approved' => '\'1\'',
                'comment_agent' => '\'\'',
                'comment_type' => '\'comment\'',
                'comment_parent' => '\'0\'',
                'user_id' => '\'0\''
            ),
            $wpdb->prefix . 'links' => array(
                'link_url' => '\'\'',
                'link_name' => '\'\'',
                'link_image' => '\'\'',
                'link_target' => '\'\'',
                'link_description' => '\'\'',
                'link_visible' => '\'Y\'',
                'link_owner' => '\'1\'',
                'link_rating' => '\'0\'',
                'link_updated' => '\'0000-00-00 00:00:00\'',
                'link_rel' => '\'\'',
                'link_rss' => '\'\''
            ),
            $wpdb->prefix . 'options' => array(
                'option_name' => '\'\'',
                'autoload' => '\'yes\''
            ),
            $wpdb->prefix . 'postmeta' => array(
                'post_id' => '\'0\'',
                'meta_key' => 'NULL'
            ),
            $wpdb->prefix . 'posts' => array(
                'post_author' => '\'0\'',
                'post_date_gmt' => '\'0000-00-00 00:00:00\'',
                'post_date' => '\'0000-00-00 00:00:00\'',
                'post_status' => '\'publish\'',
                'comment_status' => '\'open\'',
                'ping_status' => '\'open\'',
                'post_password' => '\'\'',
                'post_name' => '\'\'',
                'post_modified_gmt' => '\'0000-00-00 00:00:00\'',
                'post_modified' => '\'0000-00-00 00:00:00\'',
                'post_parent' => '\'0\'',
                'guid' => '\'\'',
                'menu_order' => '\'0\'',
                'post_type' => '\'post\'',
                'post_mime_type' => '\'\'',
                'comment_count' => '\'0\''
            ),
            $wpdb->prefix . 'term_relationships' => array(
                'term_order' => '0'
            ),
            $wpdb->prefix . 'term_taxonomy' => array(
                'taxonomy' => '\'\'',
                'parent' => '0',
                'count' => '0'
            ),
            $wpdb->prefix . 'terms' => array(
                'name' => '\'\'',
                'slug' => '\'\'',
                'term_group' => '0'
            ),
            $wpdb->prefix . 'users' => array(
                'user_login' => '\'\'',
                'user_pass' => '\'\'',
                'user_nicename' => '\'\'',
                'user_email' => '\'\'',
                'user_url' => '\'\'',
                'user_registered' => '\'0000-00-00 00:00:00\'',
                'user_activation_key' => '\'\'',
                'user_status' => '\'0\'',
                'display_name' => '\'\'',
                // for network install
                'spam' => '\'0\'',
                'deleted' => '\'0\''
            ),
            $wpdb->prefix . 'usermeta' => array(
                'user_id' => '\'0\'',
                'meta_key' => 'NULL',
            ),
            // for network install
            $wpdb->prefix . 'blog_versions' => array(
                'blog_id' => '\'0\'',
                'db_version' => '\'\'',
                'last_updated' => '\'0000-00-00 00:00:00\''
            ),
            $wpdb->prefix . 'blogs' => array(
                'site_id' => '\'0\'',
                'domain' => '\'\'',
                'path' => '\'\'',
                'registered' => '\'0000-00-00 00:00:00\'',
                'last_updated' => '\'0000-00-00 00:00:00\'',
                'public' => '\'1\'',
                'mature' => '\'0\'',
                'spam' => '\'0\'',
                'deleted' => '\'0\'',
                'lang_id' => '\'0\''
            ),
            $wpdb->prefix . 'registration_log' => array(
                'email' => '\'\'',
                'IP' => '\'\'',
                'blog_id' => '\'0\'',
                'date_registered' => '\'0000-00-00 00:00:00\''
            ),
            $wpdb->prefix . 'signups' => array(
                'domain' => '\'\'',
                'path' => '\'\'',
                'user_login' => '\'\'',
                'user_email' => '\'\'',
                'registered' => '\'0000-00-00 00:00:00\'',
                'activated' => '\'0000-00-00 00:00:00\'',
                'active' => '\'0\'',
                'activation_key' => '\'\'',
            ),
            $wpdb->prefix . 'site' => array(
                'domain' => '\'\'',
                'path' => '\'\''
            ),
            $wpdb->prefix . 'sitemeta' => array(
                'site_id' => '\'0\'',
                'meta_key' => 'NULL',
            )
        );
        $tables = $wpdb->tables('all');
        //var_dump($tables);
        //echo '<pre>';
        //var_dump($columns_to_check);
        foreach ($tables as $table) {
            $col_infos = $wpdb->get_results("SHOW COLUMNS FROM $table");
            foreach ($col_infos as $col) {
                if (!empty($columns_to_check[$table]) 
                        && array_key_exists($col->Field, $columns_to_check[$table]) 
                        && $col->Default != $columns_to_check[$table][$col->Field]) {
                    $results_table[$table] = 'damaged: field "'.$col->Field. '" has default value '.$col->Default.' should be '.$columns_to_check[$table][$col->Field];
                    break;
                }
            }
        }
        if (empty($results_table)) {
            return true;
        } else {
            return $results_table;
        }
    }

    /**
     * Method to do the fixing job to the broken tables.
     *
     * If the job succeeded, it returns string of success message.
     * If failed, it returns the array of the failed query for debugging.
     *
     * @return string|array of string
     * @access private
     */
    public static function do_fix_database() {
        global $wpdb, $wp_version, $utils;
        $global_schema_to_change = array(
            $wpdb->prefix . 'commentmeta' => array(
                "comment_id bigint(20) unsigned NOT NULL default '0'",
                "meta_key varchar(255) default NULL",
                "meta_value longtext"
            ),
            $wpdb->prefix . 'comments' => array(
                "comment_post_ID bigint(20) unsigned NOT NULL default '0'",
                "comment_author_email varchar(100) NOT NULL default ''",
                "comment_author_url varchar(200) NOT NULL default ''",
                "comment_author_IP varchar(100) NOT NULL default ''",
                "comment_date datetime NOT NULL default '0000-00-00 00:00:00'",
                "comment_date_gmt datetime NOT NULL default '0000-00-00 00:00:00'",
                "comment_karma int(11) NOT NULL default '0'",
                "comment_approved varchar(20) NOT NULL default '1'",
                "comment_agent varchar(255) NOT NULL default ''",
                "comment_type varchar(20) NOT NULL default ''",
                "comment_parent bigint(20) unsigned NOT NULL default '0'",
                "user_id bigint(20) unsigned NOT NULL default '0'"
            ),
            $wpdb->prefix . 'links' => array(
                "link_url varchar(255) NOT NULL default ''",
                "link_name varchar(255) NOT NULL default ''",
                "link_image varchar(255) NOT NULL default ''",
                "link_target varchar(25) NOT NULL default ''",
                "link_description varchar(255) NOT NULL default ''",
                "link_visible varchar(20) NOT NULL default 'Y'",
                "link_owner bigint(20) unsigned NOT NULL default '1'",
                "link_rating int(11) NOT NULL default '0'",
                "link_updated datetime NOT NULL default '0000-00-00 00:00:00'",
                "link_rel varchar(255) NOT NULL default ''",
                "link_notes mediumtext NOT NULL",
                "link_rss varchar(255) NOT NULL default ''"
            ),
            $wpdb->prefix . 'options' => array(
                "option_name varchar(64) NOT NULL default ''",
                "option_value longtext NOT NULL",
                "autoload varchar(20) NOT NULL default 'yes'"
            ),
            $wpdb->prefix . 'postmeta' => array(
                "post_id bigint(20) unsigned NOT NULL default '0'",
                "meta_key varchar(255) default NULL",
                "meta_value longtext"
            ),
            $wpdb->prefix . 'posts' => array(
                "post_author bigint(20) unsigned NOT NULL default '0'",
                "post_date datetime NOT NULL default '0000-00-00 00:00:00'",
                "post_date_gmt datetime NOT NULL default '0000-00-00 00:00:00'",
                "post_status varchar(20) NOT NULL default 'publish'",
                "comment_status varchar(20) NOT NULL default 'open'",
                "ping_status varchar(20) NOT NULL default 'open'",
                "post_password varchar(20) NOT NULL default ''",
                "post_name varchar(200) NOT NULL default ''",
                "post_modified datetime NOT NULL default '0000-00-00 00:00:00'",
                "post_modified_gmt datetime NOT NULL default '0000-00-00 00:00:00'",
                "post_content_filtered longtext NOT NULL",
                "post_parent bigint(20) unsigned NOT NULL default '0'",
                "guid varchar(255) NOT NULL default ''",
                "menu_order int(11) NOT NULL default '0'",
                "post_type varchar(20) NOT NULL default 'post'",
                "post_mime_type varchar(100) NOT NULL default ''",
                "comment_count bigint(20) NOT NULL default '0'"
            ),
            $wpdb->prefix . 'term_relationships' => array(
                "term_order int(11) NOT NULL default 0"
            ),
            $wpdb->prefix . 'term_taxonomy' => array(
                "taxonomy varchar(32) NOT NULL default ''",
                "description longtext NOT NULL",
                "parent bigint(20) unsigned NOT NULL default 0",
                "count bigint(20) NOT NULL default 0"
            ),
            $wpdb->prefix . 'terms' => array(
                "name varchar(200) NOT NULL default ''",
                "slug varchar(200) NOT NULL default ''",
                "term_group bigint(10) NOT NULL default 0"
            ),
            $wpdb->prefix . 'users' => array(
                "user_login varchar(60) NOT NULL default ''",
                "user_pass varchar(64) NOT NULL default ''",
                "user_nicename varchar(50) NOT NULL default ''",
                "user_email varchar(100) NOT NULL default ''",
                "user_url varchar(100) NOT NULL default ''",
                "user_registered datetime NOT NULL default '0000-00-00 00:00:00'",
                "user_activation_key varchar(60) NOT NULL default ''",
                "user_status int(11) NOT NULL default '0'",
                "display_name varchar(250) NOT NULL default ''"
            ),
            $wpdb->prefix . 'usermeta' => array(
                "user_id bigint(20) unsigned NOT NULL default '0'",
                "meta_key varchar(255) default NULL",
                "meta_value longtext"
            )
        );

        $network_schema_to_change = array(
            $wpdb->prefix . 'blog_versions' => array(
                "blog_id bigint(20) NOT NULL default '0'",
                "db_version varchar(20) NOT NULL default ''",
                "last_updated datetime NOT NULL default '0000-00-00 00:00:00'"
            ),
            $wpdb->prefix . 'blogs' => array(
                "site_id bigint(20) NOT NULL default '0'",
                "domain varchar(200) NOT NULL default ''",
                "path varchar(100) NOT NULL default ''",
                "registered datetime NOT NULL default '0000-00-00 00:00:00'",
                "last_updated datetime NOT NULL default '0000-00-00 00:00:00'",
                "public tinyint(2) NOT NULL default '1'",
                "mature tinyint(2) NOT NULL default '0'",
                "spam tinyint(2) NOT NULL default '0'",
                "deleted tinyint(2) NOT NULL default '0'",
                "lang_id int(11) NOT NULL default '0'"
            ),
            $wpdb->prefix . 'registration_log' => array(
                "email varchar(255) NOT NULL default ''",
                "IP varchar(30) NOT NULL default ''",
                "blog_id bigint(20) NOT NULL default '0'",
                "date_registered datetime NOT NULL default '0000-00-00 00:00:00'"
            ),
            $wpdb->prefix . 'signups' => array(
                "domain varchar(200) NOT NULL default ''",
                "path varchar(100) NOT NULL default ''",
                "title longtext NOT NULL",
                "user_login varchar(60) NOT NULL default ''",
                "user_email varchar(100) NOT NULL default ''",
                "registered datetime NOT NULL default '0000-00-00 00:00:00'",
                "activated datetime NOT NULL default '0000-00-00 00:00:00'",
                "active tinyint(1) NOT NULL default '0'",
                "activation_key varchar(50) NOT NULL default ''",
                "meta longtext"
            ),
            $wpdb->prefix . 'site' => array(
                "domain varchar(200) NOT NULL default ''",
                "path varchar(100) NOT NULL default ''"
            ),
            $wpdb->prefix . 'sitemeta' => array(
                "site_id bigint(20) NOT NULL default '0'",
                "meta_key varchar(255) default NULL",
                "meta_value longtext"
            ),
            $wpdb->prefix . 'users' => array(
                "user_login varchar(60) NOT NULL default ''",
                "spam tinyint(2) NOT NULL default '0'",
                "deleted tinyint(2) NOT NULL default '0'"
            )
        );
        if (version_compare($wp_version, '3.6', '<'))
            return false;
        $return_val = array();
        $queries = array();
        $results = self::sanity_check();
        if ($results !== true) {
            if (!self::maintenance_backup()) {
                $message = __('Can\'t create backup file.', 'sqlite-db');
                return $message;
            }
            $tables = array_keys($results);
            foreach ($tables as $table) {
                if (key_exists($table, $global_schema_to_change)) {
                    $queries = $global_schema_to_change[$table];
                }
                if (key_exists($table, $network_schema_to_change)) {
                    if (!empty($queries)) {
                        $queries = array_merge($queries, $network_schema_to_change[$table]);
                    } else {
                        $queries = $network_schema_to_change[$table];
                    }
                }
                foreach ($queries as $query) {
                    $sql = 'ALTER TABLE' . ' ' . $table . ' ' . 'CHANGE COLUMN' . ' ' . $query;
                    $res = $wpdb->query($sql);
                    if ($res === false) {
                        $return_val[] = __('Failed: ', 'sqlite-db') . $query;
                    }
                }
            }
        } else {
            $message = __('Your database is OK. You don\'t have to restore it.', 'sqlite-db');
            return $message;
        }
        if (empty($return_val)) {
            $message = __('Your database restoration is successfully finished!', 'sqlite-db');
            return $message;
        } else {
            return $return_val;
        }
    }

    /**
     * Method to return the result of SHOW COLUMNS query.
     *
     * It returns the result of SHOW COLUMNS query as an object if the table exists.
     * It returns the error message if the table doesn't exist.
     *
     * @return string|object
     */
    private function show_columns() {
        global $wpdb, $utils;
        $domain = $utils->text_domain;
        $tables = $wpdb->tables('all');
        if (!isset($_POST['table'])) {
            $message = __('Table name is not selected.', 'sqlite-db');
            return $message;
        } elseif (!in_array($_POST['table'], $tables)) {
            $message = __('There\'s no such table.', 'sqlite-db');
            return $message;
        } else {
            $table_name = $_POST['table'];
            $results = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
            return $results;
        }
    }

    /**
     * Method to create a back up file of the database.
     *
     * It returns true if success, false if failure.
     *
     * @return boolean
     */
    public static function maintenance_backup() {
        $result = array();
        $database_file = FQDB;
        $db_name = basename(FQDB);
        if (!file_exists($database_file)) {
            return false;
        }
        $today = date("Ymd-His");
        if (!extension_loaded('zip')) {
            $backup_file = $database_file . '.' . $today . '.maintenance-backup';
            if (copy($database_file, $backup_file)) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $backup_file = $database_file . '.' . $today . '.maintenance-backup.zip';
            $zip = new \ZipArchive();
            $res = $zip->open($backup_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($res === true) {
                $zip->addFile($database_file, $db_name);
                $result = true;
            } else {
                $result = false;
            }
            $zip->close();
        }
        return $result;
    }

    /*
        if (isset($_POST['show-columns'])) {
            check_admin_referer('sqliteintegration-database-manip-stats');
            if (is_multisite() && !current_user_can('manage_network_options')) {
                die(__('You are not allowed to do this operation!', 'sqlite-db'));
            } elseif (!current_user_can('manage_options')) {
                die(__('You are not allowed to do this operation!', 'sqlite-db'));
            }
            $results = $this->show_columns();
            if (is_array($results)) {
                $title = '<h3>' . sprintf(__('Columns In %s', 'sqlite-db'), $_POST['table']) . '</h3>';
                $column_header = __('Column', 'sqlite-db');
                $type_header = __('Type', 'sqlite-db');
                $null_header = __('Null', 'sqlite-db');
                $default_header = __('Default', 'sqlite-db');
                echo '<div class="wrap" id="sqlite-admin-side-wrap" style="clear: both;">';
                echo $title;
                echo '<table class="widefat page fixed"><thead><tr><th>' . $column_header . '</th><th>' . $type_header . '</th><th>' . $null_header . '</th><th>' . $default_header . '</th></tr></thead>';
                echo '<tbody>';
                $counter = 0;
                foreach ($results as $column) {
                    echo (($counter % 2) == 1) ? '<tr class="alt">' : '<tr>';
                    echo '<td>' . $column->Field . '</td>';
                    echo '<td>' . $column->Type . '</td>';
                    echo '<td>' . $column->Null . '</td>';
                    echo '<td>' . $column->Default . '</td>';
                    echo '</tr>';
                    $counter++;
                }
                echo '</tbody></table></div>';
            } else {
                $title = '<h3>' . __('Columns Info', 'sqlite-db') . '</h3>';
                echo '<div class="wrap" id="sqlite-admin-side-wrap">';
                echo $title;
                echo '<p>' . $results;
                echo '</p></div>';
            }
        }
     * 
     */

}
?>