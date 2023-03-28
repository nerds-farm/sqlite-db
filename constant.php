<?php
/*
 * FQDBDIR is a directory where the sqlite database file is placed.
 * If DB_DIR is defined, it is used as FQDBDIR.
 */
if (defined('DB_DIR')) {
    $FQDBDIR = DB_DIR;
    if (substr(DB_DIR, -1, 1) != DIRECTORY_SEPARATOR) {
        $FQDBDIR .= DIRECTORY_SEPARATOR;
    }
} else {
    if (defined('WP_CONTENT_DIR')) {
        $FQDBDIR = WP_CONTENT_DIR;
    } else {
        $FQDBDIR = ABSPATH . 'wp-content';    
    }
    $FQDBDIR .= DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR;
}
$FQDBDIR = str_replace('/', DIRECTORY_SEPARATOR, $FQDBDIR);
define('FQDBDIR', $FQDBDIR);

if (!defined('SQLITE_DB_PATH')) {
    define('SQLITE_DB_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR);
}

/*
 * FQDB is a database file name. If DB_FILE is defined, it is used
 * as FQDB.
 */
if (defined('DB_NAME')) {
    define('FQDB', FQDBDIR . DB_NAME.'.sqlite');
} else {
    define('FQDB', FQDBDIR . '.ht.sqlite');
}

// Define SQLite constant.
if ( ! defined( 'DATABASE_TYPE' ) ) {
	define( 'DATABASE_TYPE', 'mysql' );
}

if (!defined('DB_PATH')) {
    define('DB_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR);
}