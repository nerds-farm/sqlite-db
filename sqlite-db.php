<?php

/*
  Plugin Name: SQLite DB
  Plugin URI: http://nerds.farm/plugins/sqlite-db
  Description: SQLite Integration is the plugin that enables WordPress to use SQLite. If you don't have MySQL and want to build a WordPress website, it's for you.
  Author: Nerds Farms
  Version: 2.0
  Author URI: http://nerds.farm
  Text Domain: sqlite-db
  Domain Path: /languages
  License: GPL3 or later
 */

/* Copyright 2013-2014 Kojima Toshiyasu (email: kjm@dogwood.skr.jp)
 * Copyright 2022 Nerds Farm
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 */

/**
 * This file defines global constants and defines SQLite DB class.
 *
 * @package SQLite DB
 * @author Nerds Farm
 */
if (!defined('ABSPATH')) {
    echo 'Thank you, but you are not allowed to access this file.';
    die();
}
/*
 * This will be activated after the installation is finished.
 * So you can use all the functionality of WordPress.
 */
$siteurl = get_option('siteurl');
/*
 * Defines basic constants.
 */
define('SQLITE_DB_VERSION', '2.0');
define('SQLITE_DB_DIR', dirname(plugin_basename(__FILE__)));
if (!defined('SQLITE_DB_PATH')) {
    define('SQLITE_DB_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR);
}
define('SQLITE_DB_URL', WP_PLUGIN_URL . '/' . SQLITE_DB_DIR . '/');

if (!defined('DB_PDO')) {
    define( 'DB_PDO', 'mysql' );
}
if (!defined('DB_PATH')) {
    define('DB_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR);
}

define('SQLITE_PATCH_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'patches');
require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'utils.php';
require_once SQLITE_DB_PATH . 'functions.php';

/*
 * Instantiates utility classes.
 */
if (!class_exists('SQLiteIntegrationUtils')) {
    require_once SQLITE_DB_PATH . 'utilities' . DIRECTORY_SEPARATOR . 'utility.php';
    $utils = new SQLiteIntegrationUtils();
}
if (!class_exists('SQLiteIntegrationDocument')) {
    require_once SQLITE_DB_PATH . 'utilities' . DIRECTORY_SEPARATOR . 'documentation.php';
    $doc = new SQLiteIntegrationDocument();
}
if (!class_exists('PatchUtils')) {
    require_once SQLITE_DB_PATH . 'utilities' . DIRECTORY_SEPARATOR . 'patch.php';
    $patch_utils = new PatchUtils();
}
if (!class_exists('SQLiteDbMaintenance')) {
    require_once SQLITE_DB_PATH . 'utilities' . DIRECTORY_SEPARATOR . 'maintenance.php';
    $maintenance = new SQLiteDbMaintenance();
}


require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'sqlite-db.php';
/* this is enough for initialization */
new \SQLiteDb();
?>