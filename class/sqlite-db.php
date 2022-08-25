<?php

/**
 * This class is for WordPress Administration Panel.
 *
 * This class and other utility classes don't affect the base functionality
 * of the plugin.
 *
 */
class SQLiteDb {

    /**
     * Constructor.
     *
     * This constructor does everything needed for the administration panel.
     *
     * @param no parameter is provided.
     */
    public function __construct() {

        if (function_exists('register_activation_hook')) {
            register_activation_hook(__FILE__, array($this, 'install'));
        }
        if (function_exists('register_deactivation_hook')) {
            ;
        }
        if (function_exists('register_uninstall_hook')) {
            register_uninstall_hook(__FILE__, array('SQLiteIntegration', 'uninstall'));
        }
        if (function_exists('is_multisite') && is_multisite()) {
            add_action('network_admin_menu', array($this, 'add_network_pages'));
            add_action('network_admin_notices', array('SQLiteIntegrationUtils', 'show_admin_notice'));
        } else {
            add_action('admin_menu', array($this, 'add_pages'));
            add_action('admin_notices', array('SQLiteIntegrationUtils', 'show_admin_notice'));
        }
        // See the docstring for download_backup_db() in utilities/utility.php
        // We need this registration process.
        add_action('admin_init', array($this, 'exec_actions'));
        add_action('admin_init', array('SQLiteIntegrationUtils', 'download_backup_db'));
        add_action('plugins_loaded', array($this, 'textdomain_init'));
    }

    /**
     * Method to install on multisite or single site.
     *
     * There really is nothing to install for now. It is for future use...
     *
     * @param no parameter is provided.
     * @return returns null.
     */
    public function install() {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite()) {
            $old_blog = $wpdb->blogid;
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $this->_install();
            }
            switch_to_blog($old_blog);
            return;
        } else {
            $this->_install();
            return;
        }
    }

    /**
     * Method to install something.
     *
     * We have nothing to do for now.
     * We show menu and documents only to the network administrator.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function _install() {
        
    }

    /**
     * Method to uninstall plugin.
     *
     * This will remove wp-content/db.php and wp-content/patches direcotry.
     * If you migrate the site to the sever with MySQL, you have only to
     * migrate the data in the database.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function uninstall() {
        // remove patch files and patch directory
        if (file_exists(SQLITE_PATCH_DIR) && is_dir(SQLITE_PATCH_DIR)) {
            $dir_handle = opendir(SQLITE_PATCH_DIR);
            while (($file_name = readdir($dir_handle)) !== false) {
                if ($file_name != '.' && $file_name != '..') {
                    unlink(SQLITE_PATCH_DIR . DIRECTORY_SEPARATOR . $file_name);
                }
            }
            rmdir(SQLITE_PATCH_DIR);
        }
        // remove wp-content/db.php
        if (defined('WP_CONTENT_DIR')) {
            $target = WP_CONTENT_DIR . 'db.php';
        } else {
            $target = ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'db.php';
        }
        if (file_exists($target)) {
            unlink($target);
        }
    }

    /**
     * Method to manipulate the admin panel, stylesheet and JavaScript.
     *
     * We use class method to show pages and want to load style files and script
     * files only in our plugin documents, so we need add_submenu_page with parent
     * slug set to null. This means that menu items are added but hidden from the
     * users.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function add_pages() {
        global $utils, $doc, $patch_utils, $maintenance;
        if (function_exists('add_options_page')) {
            $welcome_page = add_options_page(__('SQLite Integration'), __('SQLite DB'), 'manage_options', 'sqlite-db', array($utils, 'welcome'));
            $util_page = add_submenu_page(null, 'System Info', 'System Info', 'manage_options', 'sys-info', array($utils, 'show_utils'));
            $edit_db = add_submenu_page(null, 'Setting File', 'Setting File', 'manage_options', 'setting-file', array($utils, 'edit_db_file'));
            $doc_page = add_submenu_page(null, 'Documentation', 'Documentation', 'manage_options', 'doc', array($doc, 'show_doc'));
            $patch_page = add_submenu_page(null, 'Patch Utility', 'Patch Utility', 'manage_options', 'patch', array($patch_utils, 'show_patch_page'));
            $maintenance_page = add_submenu_page(null, 'DB Maintenance', 'DB Maintenance', 'manage_options', 'maintenance', array($maintenance, 'show_maintenance_page'));
            $explorer_page = add_submenu_page(null, 'DB Explorer', 'DB Explorer', 'manage_options', 'explorer', array($this, 'show_explorer_page'));
        }
    }

    /**
     * Method to manipulate network admin panel.
     *
     * Capability is set to manage_network_options.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function add_network_pages() {
        global $utils, $doc, $patch_utils, $maintenance;
        if (function_exists('add_options_page')) {
            $welcome_page = add_submenu_page('settings.php', __('SQLite Integration'), __('SQLite Integration'), 'manage_network_options', 'sqlite-db', array($utils, 'welcome'));
            $util_page = add_submenu_page(null, 'System Info', 'System Info', 'manage_network_options', 'sys-info', array($utils, 'show_utils'));
            $edit_db = add_submenu_page(null, 'Setting File', 'Setting File', 'manage_network_options', 'setting-file', array($utils, 'edit_db_file'));
            $doc_page = add_submenu_page(null, 'Documentation', 'Documentation', 'manage_network_options', 'doc', array($doc, 'show_doc'));
            $patch_page = add_submenu_page(null, 'Patch Utility', 'Patch Utility', 'manage_network_options', 'patch', array($patch_utils, 'show_patch_page'));
            $maintenance_page = add_submenu_page(null, 'DB Maintenance', 'DB Maintenance', 'manage_network_options', 'maintenance', array($maintenance, 'show_maintenance_page'));
            $explorer_page = add_submenu_page(null, 'DB Explorer', 'DB Explorer', 'manage_options', 'explorer', array($this, 'show_explorer_page'));
            /*
              add_action('admin_print_scripts-' . $util_page, array($this, 'add_sqlite_script'));
              add_action('admin_print_scripts-' . $doc_page, array($this, 'add_sqlite_script'));
              add_action('admin_print_scripts-' . $patch_page, array($this, 'add_sqlite_script'));
              add_action('admin_print_scripts-' . $edit_db, array($this, 'add_sqlite_script'));

              add_action('admin_print_styles-'.$welcome_page, array($this, 'add_style_sheet'));
              add_action('admin_print_styles-'.$util_page, array($this, 'add_style_sheet'));
              add_action('admin_print_styles-'.$edit_db, array($this, 'add_style_sheet'));
              add_action('admin_print_styles-'.$doc_page, array($this, 'add_style_sheet'));
              add_action('admin_print_styles-'.$patch_page, array($this, 'add_style_sheet'));
              add_action('admin_print_styles-'.$maintenance_page, array($this, 'add_style_sheet'));
             */
        }
    }

    /**
     * Method to initialize textdomain.
     *
     * Japanese catalog is only available.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function textdomain_init() {
        global $utils;
        //$current_locale = get_locale();
        //if (!empty($current_locale)) {
        //  $moFile = dirname(__FILE__) . "/languages/sqlite-wordpress-" . $current_locale . ".mo";
        //  if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('sqlite-wordpress', $moFile);
        //}
        load_plugin_textdomain($utils->text_domain, false, SQLITE_DB_DIR . DIRECTORY_SEPARATOR. 'languages'. DIRECTORY_SEPARATOR);
    }

    /**
     * Method to initialize stylesheet on the admin panel.
     *
     * This determines which stylesheet to use depending on the users' choice
     * of admin_color. Each stylesheet imports style.css and change the color
     * of the admin dashboard.
     *
     * @param no parameter is provided.
     * @return no return values.
     */
    public function add_style_sheet() {
        global $current_user;
        wp_get_current_user();
        $admin_color = get_user_meta($current_user->ID, 'admin_color', true);
        if ($admin_color == 'fresh') {
            $stylesheet_file = 'style.min.css';
        } else {
            $stylesheet_file = $admin_color . '.min.css';
        }
        $style_url = SQLITE_DB_URL . '/styles/' . $stylesheet_file;
        $style_file = SQLITE_DB_PATH . '/styles/' . $stylesheet_file;
        if (file_exists($style_file)) {
            wp_enqueue_style('sqlite_integration_stylesheet', $style_url);
        }
    }

    /**
     * Method to register the JavaScript file.
     *
     * To register the JavaScript file. It's only for the admin dashboard.
     * It won't included in web pages.
     *
     * @param no parameter is provided.
     * @return no return value.
     */
    public function add_sqlite_script() {
        $script_url = SQLITE_DB_URL . '/js/sqlite.min.js';
        $script_file = SQLITE_DB_PATH . '/js/sqlite.min.js';
        if (file_exists($script_file)) {
            wp_enqueue_script('sqlite-integration', $script_url, 'jquery');
        }
    }

    public function show_explorer_page() {
        $explorer_file = SQLITE_DB_PATH . DIRECTORY_SEPARATOR . 'utilities' . DIRECTORY_SEPARATOR . 'explorer.php';
        if (file_exists($explorer_file)) {
            include_once($explorer_file);
        }
    }

    public function exec_actions() {
        if (!empty($_GET['page']) && $_GET['page'] == 'sqlite-db') {
            if (!empty($_GET['action'])) {

                switch ($_GET['action']) {
                    case 'convert':
                        include_once SQLITE_DB_PATH . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'mysql2sqlite.php';
                        $db_mysql = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . DB_NAME . '.sqlite';
                        if (file_exists($db_mysql)) {
                            rename($db_mysql, WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . DB_NAME . '_'.time().'.sqlite');
                        }
                        $args = ['sqlitedb' => $db_mysql];
                        $converter = new \Mysql2Sqlite($args);
                        $converter->run();
                        break;
                    case 'set':
                        if (!empty($_GET['db_sqlite'])) {
                            $db_sqlite = str_replace('\\\\', '\\', $_GET['db_sqlite']);
                            $this->wp_update_global_config('DB_SQLITE', $db_sqlite);
                        }
                        break;
                    case 'switch':
                        $db_php = 'db.php';
                        $db_sqlite_plugin_php = SQLITE_DB_DIR.DIRECTORY_SEPARATOR.$db_php; // sqlite-db/db.php
                        $db_file = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $db_php;
                        $db_plugin = SQLITE_DB_PATH . $db_php;
                        $db_plugin = str_replace('/', DIRECTORY_SEPARATOR, $db_plugin);
                        $db_require = 'require_once "'.$db_plugin.'";';
                        if (file_exists($db_file)) {
                            $db_file_content = file_get_contents($db_file);
                            if (strpos($db_file_content, $db_sqlite_plugin_php) === false) {
                                $db_file_content = '<?php '.$db_require.' ?>'.PHP_EOL.$db_file_content;
                                file_put_contents($db_file, $db_file_content);
                            }
                            if (defined('DB_PDO') && DB_PDO == 'sqlite') {
                                $this->wp_update_global_config('DB_PDO', 'mysql');
                            } else {
                                $this->wp_update_global_config('DB_PDO', 'sqlite');
                            }
                            // check if its own db.php file (find DP_PDO constant)
                            // otherwise append require_once db.php
                        } else {
                            file_put_contents($db_file, '<?php'.PHP_EOL.$db_require);
                            //copy($db_plugin, $db_file);
                        }
                        
                        break;
                }

                /*
                  // dump
                  include_once SQLITE_DB_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
                  try {
                  $dump = new \Ifsnop\Mysqldump\Mysqldump('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
                  $dump->start(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'database'. DIRECTORY_SEPARATOR.DB_NAME.'.sql');
                  } catch (\Exception $e) {
                  echo 'mysqldump-php error: ' . $e->getMessage();
                  }
                 */
                $redirect_url = admin_url('options-general.php?page=sqlite-db');
                //echo '<script>window.location.href = "'. $redirect_url .'";</script>';
                // switch USE_MYSQL value in config.php
                wp_redirect($redirect_url);
            }
        }
    }

    function wp_update_global_config($key, $value) {

        if (file_exists(ABSPATH . 'wp-config.php')) {
            $global_config_file = ABSPATH . 'wp-config.php';
        } else {
            $global_config_file = dirname(ABSPATH) . '/wp-config.php';
        }

        $half = "define( '" . $key . "',";
        $line = $half . " '" . $value . "' );";
        if ($this->is_writeable_ACLSafe($global_config_file)) {
            //read the entire string
            $str = file_get_contents($global_config_file);

            if (strpos($str, $half) !== false) {
                // update
                if (defined('DB_SQLITE')) {
                    $str = str_replace($half . " '" . constant($key) . "' );", $line, $str);
                }
            } else {
                // add
                $db_name = "define( 'DB_NAME',";
                $str = str_replace($db_name, $line . PHP_EOL . $db_name, $str);
            }

            //write the entire string
            file_put_contents($global_config_file, $str);
        }
        return true;
    }

    // from legolas558 d0t users dot sf dot net at http://www.php.net/is_writable
    function is_writeable_ACLSafe($path) {

        if (
                ( defined('PHP_OS_FAMILY') && 'Windows' !== constant('PHP_OS_FAMILY') ) ||
                stristr(PHP_OS, 'DAR') ||
                !stristr(PHP_OS, 'WIN')
        ) {
            return is_writeable($path);
        }

        // PHP's is_writable does not work with Win32 NTFS

        if ($path[strlen($path) - 1] == '/') { // recursively return a temporary file path
            return $this->is_writeable_ACLSafe($path . uniqid(mt_rand()) . '.tmp');
        } elseif (is_dir($path)) {
            return $this->is_writeable_ACLSafe($path . '/' . uniqid(mt_rand()) . '.tmp');
        }

        // check tmp file for read/write capabilities
        $rm = file_exists($path);
        $f = @fopen($path, 'a');
        if ($f === false)
            return false;
        fclose($f);
        if (!$rm) {
            unlink($path);
        }

        return true;
    }

}
