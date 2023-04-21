<?php

/*
  Plugin Name: SQLite DB
  Plugin URI: http://nerds.farm/plugins/sqlite-db
  Description: SQLite DB is the plugin that enables WordPress to use SQLite. If you don't have MySQL and want to build a WordPress website, it's for you.
  Author: frapesce, nerdsfarm
  Version: 1.0
  Author URI: http://nerds.farm
  Text Domain: sqlite-db
  Domain Path: /languages
  License: GPL3 or later
 */

/* Copyright 2013-2014 Kojima Toshiyasu (email: kjm@dogwood.skr.jp) */
/* Copyright 2023 WordPress Performance Team */
/* Copyright 2023 Nerds Farm */
/*
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 */

if (!defined('ABSPATH')) {
    echo 'Thank you, but you are not allowed to access this file.';
    die();
}

/**
 * This file defines global constants and defines SQLite DB class.
 *
 * @package SQLite DB
 * @author Nerds Farm
 */
require_once('constant.php');
define('SQLITE_DB_VERSION', '1.0');
define('SQLITE_DB_DIR', dirname(plugin_basename(__FILE__)));
define('SQLITE_DB_URL', WP_PLUGIN_URL . '/' . SQLITE_DB_DIR . '/');
define('SQLITE_PATCH_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'patches');

require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'utils.php';
//require_once SQLITE_DB_PATH . 'functions.php';

$vendor = SQLITE_DB_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
}

require_once SQLITE_DB_PATH . 'class' . DIRECTORY_SEPARATOR . 'sqlite-db.php';
/* this is enough for initialization */
new \SQLiteDB\SQLiteDb();
?>