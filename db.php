<?php

/**
 * This file defines some constant required for SQLite Integration.
 *
 * This file must be placed in the directory wordpress/wp-content/db.php.
 * WordPress loads this file automatically.
 *
 * @version 1.8.1
 * @package SQLite Integration
 * @author Kojima Toshiyasu
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

require_once('constant.php');

if (defined('USE_MYSQL') && USE_MYSQL)
    return;
if (!defined('DATABASE_TYPE') || (defined('DATABASE_TYPE') && DATABASE_TYPE != 'sqlite'))
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

// If the SQLite3 class does not exist, then the module cannot be activated.
if ( ! class_exists( 'SQLite3' ) ) {
    pdo_log_error('SQLite3 Class is missing.', 'Your PHP installtion appears not to have the right PDO drivers loaded. These are required for this version of WordPress and the type of database you have specified.');
}

/*
 * Notice:
 * Your scripts have the permission to create directories or files on your server.
 * If you write in your wp-config.php like below, we take these definitions.
 * define('DB_DIR', '/full_path_to_the_database_directory/');
 * define('DB_FILE', 'database_file_name');
 */



require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'pdodb.php';
?>