<?php

/**
 * This file defines some constant required for SQLite DB.
 *
 * This file must be placed in the directory wordpress/wp-content/db.php.
 * WordPress loads this file automatically.
 *
 * @version 2.0
 * @package SQLite DB
 * @author Nerds Farm
 *
 */
if (!defined('ABSPATH')) { // Oh, you are not WordPress!
    echo 'Thank you, but you are not allowed to access here.';
    die();
}

/*
 * USE_MYSQL is a directive for using MySQL for database.
 * If you want to change the database from SQLite to MySQL or from MySQL to SQLite,
 * the line below in the wp-config.php will enable you to use MySQL.
 *
 * <code>
 * define('USE_MYSQL', true);
 * </code>
 *
 * If you want to use SQLite, the line below will do. Or simply removing the line will
 * be enough.
 *
 * <code>
 * define('USE_MYSQL', false);
 * </code>
 */
if (defined('DB_PDO') && DB_PDO != 'sqlite')
    return;

function pdo_log_error($message, $data = null) {
    if (strpos($_SERVER['SCRIPT_NAME'], 'wp-admin') !== false) {
        $admin_dir = '';
    } else {
        $admin_dir = 'wp-admin/';
    }
    die(<<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>WordPress &rsaquo; Error</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="{$admin_dir}css/install.css" type="text/css" />
</head>
<body>
  <h1 id="logo"><img alt="WordPress" src="{$admin_dir}images/wordpress-logo.png" /></h1>
  <p>$message</p>
  <p>$data</p>
</body>
<html>

HTML
    );
}

if (version_compare(PHP_VERSION, '5.2.4', '<')) {
    pdo_log_error('PHP version on this server is too old.', sprinf("Your server is running PHP version %d but this version of WordPress requires at least 5.2.4", phpversion()));
}
if (!extension_loaded('pdo')) {
    pdo_log_error('PHP PDO Extension is not loaded.', 'Your PHP installation appears to be missing the PDO extension which is required for this version of WordPress.');
}
if (!extension_loaded('pdo_sqlite')) {
    pdo_log_error('PDO Driver for SQLite is missing.', 'Your PHP installtion appears not to have the right PDO drivers loaded. These are required for this version of WordPress and the type of database you have specified.');
}

/*
 * Notice:
 * Your scripts have the permission to create directories or files on your server.
 * If you write in your wp-config.php like below, we take these definitions.
 * define('DB_DIR', '/full_path_to_the_database_directory/');
 * define('DB_FILE', 'database_file_name');
 */

/*
 * SQLITE_PDO_DIR is SQLite Integration installed directory.
 */
if (!defined('SQLITE_DB_PATH')) {
    if (defined('WP_PLUGIN_DIR')) {
        define('SQLITE_DB_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'sqlite-db' . DIRECTORY_SEPARATOR);
    } else {
        if (defined('WP_CONTENT_DIR')) {
            define('SQLITE_DB_PATH', WP_CONTENT_DIR  . DIRECTORY_SEPARATOR . 'plugins'.DIRECTORY_SEPARATOR.'sqlite-db'.DIRECTORY_SEPARATOR);
        } else {
            define('SQLITE_DB_PATH', ABSPATH . 'wp-content'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'sqlite-db'.DIRECTORY_SEPARATOR);
        }
    }
}

/*
 * DB_SQLITE_DB_PATH is a directory where the sqlite database file is placed.
 * If DB_SQLITE_DB_PATH is defined, it is used as DB_SQLITE_DB_PATH.
 */
if (!defined('DB_PATH')) {
    define('DB_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR);
}

/*
 * DB_SQLITE is a database file name. If DB_FILE is defined, it is used
 * as DB_SQLITE.
 */
if (!defined('DB_SQLITE')) {
    if (!defined('DB_NAME') || !DB_NAME) {
        define('DB_SQLITE', DB_PATH . '.ht.sqlite');
    } else {
        define('DB_SQLITE', DB_PATH . DB_NAME . '.sqlite');
    }
} else {
    if (!file_exists(DB_SQLITE)) {
        return;
    }
}

if (!defined('DB_PDO')) {
    define( 'DB_PDO', 'sqlite' );
}
require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'pdodb.php';
?>