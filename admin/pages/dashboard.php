<div class="card">
    <?php
    if (defined('DB_CONTENT')) {
        ?>
        <h2><?php _e('Disable db.php', 'sqlite-db'); ?></h2>
        <p><?php _e('If you want to totally disable the SQLite feature and plugin you have to remove the db.php file on your /wp-content folder.', 'sqlite-db'); ?></p>
        <a class="button components-button is-secondary is-destructive" href="?page=sqlite-db&action=remove" class="button"><?php _e('Remove db.php automatically', 'sqlite-db'); ?></a>
        <p><?php _e('Hint: you can remove it manually, comment or remove the include plugin row into the /wp-content/db.php file', 'sqlite-db'); ?></p>

        <?php
        if (defined('DB_ENGINE') && DB_ENGINE == 'sqlite') {
            ?>
            <p style="color: red;"><?php _e('WARNING: You actually are running under a SQLite Database, if you disable the plugin you will return to standard MySQL DB, but if the configuration on wp-config.php file is not correct it can cause an irreversible error.', 'sqlite-db'); ?></p>
            <?php
        }
    } else {
        ?>
        <h2><?php _e('Install db.php', 'sqlite-db'); ?></h2>
        <p><?php _e('You need to install the db.php file on your /wp-content folder to let the magic happens.', 'sqlite-db'); ?></p>
        <a class="button button-primary" href="?page=sqlite-db&action=install" class="button"><?php _e('Install db.php automatically', 'sqlite-db'); ?></a>
        <p><?php _e('Hint: you can install it manually, copy the file db.copy.php from inside this plugin folder into the /wp-content folder and rename it in db.php', 'sqlite-db'); ?></p>
        <?php
    }
    ?>
</div>

<div class="card">
    <h2><?php _e('Create', 'sqlite-db'); ?></h2>
    <p><?php _e('Add a NEW and EMPTY SQLite DB, a new Fresh Wordpress installation is needed if then you enable it.', 'sqlite-db'); ?></p>
    <form action="?" method="GET">
        <input type="hidden" name="page" value="sqlite-db" />
        <input type="hidden" name="action" value="create" />
        <input type="text" name="db_name" placeholder="<?php _e('New SQlite DB name*', 'sqlite-db'); ?>" required>
        <p class="small"><?php _e('* Use a clean name, no spaces or special chars are allowed', 'sqlite-db'); ?></p>
        <input type="submit" value="<?php _e('Create new Empty DB File', 'sqlite-db'); ?>" class="button">
    </form>
</div>


<?php
ob_start();
?>
<?php if (!defined('DB_ENGINE') || DB_ENGINE == 'mysql' || !defined('DB_CONTENT')) { ?>
    <p><?php _e('You are actually running your WP from a MySQL Database, so you can create a copy of current MySQL DB to a new SQLite to use later:', 'sqlite-db'); ?></p>
    <a href="?page=sqlite-db&action=migrate" class="button"><?php _e('Clone current DB from MySQL to SQLite', 'sqlite-db'); ?></a>
<?php } ?>

<?php if (defined('DB_CONTENT')) { ?>
    <?php
    if (defined('DB_ENGINE') && DB_ENGINE == 'sqlite') {
        /* TODO
          //https://github.com/vwbusguy/sqlite-to-mysql/blob/master/python/sqlite3-to-mysql.py
          <p><?php _e('You are actually running your WP from a SQLite Database, so you can create a copy of current SQLite DB to a new MySQL to use later:', 'sqlite-db'); ?></p>
          <a href="?page=sqlite-db&action=migrate" class="button"><?php _e('Clone current DB from MySQL to SQLite', 'sqlite-db'); ?></a>
         */
    }

    if (defined('DB_ENGINE') && DB_ENGINE == 'sqlite') {
        if (!defined('WP_SETUP_CONFIG')) {
            define('WP_SETUP_CONFIG', true);

            $success = false;
            if (defined('DB_USER') && DB_USER && DB_USER != 'username_here') {
                if (function_exists('mysqli_connect')) {
                    $dbh = @mysqli_init();
                    if ($dbh) {
                        @mysqli_real_connect($dbh, $host, DB_USER, DB_PASSWORD);
                        if (!$dbh->connect_errno) {
                            //@$success = mysqli_select_db( $dbh, DB_NAME );
                            $sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="' . DB_NAME . '"';
                            $query = $dbh->query($sql);
                            $row = $query->fetch_object();
                            $success = (bool) $row->exists;
                        }
                    }
                    //$success = @new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                } else {
                    @$dbh = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
                    if ($dbh) {
                        $success = @mysql_select_db(DB_NAME, $dbh);
                    }
                }
            }
            //ob_start();
            //$mysql = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
            //$link = $mysql->db_connect(false);
            //$tmp = ob_end_clean();
            //var_dump($link);
            //
            //var_dump($mysql);
            if ($success) {
                ?>
                <p><?php _e('You are actually running your WP from a SQLite Database, which have same Name of an existent MySQL DB, so you can now switch and return back to MySQL:', 'sqlite-db'); ?></p>
                <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning"><?php _e('Revert to MySQL DB'); ?> - <?php echo DB_NAME; ?></a>
                <p style="color: red;"><?php _e('WARNING: The MySQL DB is not sync with current SQSLite DB, so you could return to an old version of your site.', 'sqlite-db'); ?></p>

                <?php
            }
        }
    } else {
        if (file_exists(FQDB)) {
            ?>
            <p><?php _e('You are actually running your WP from a MySQL Database, but there is already a copy of it (same Name), so you can now switch to SQLite:', 'sqlite-db'); ?></p>
            <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning"><?php _e('Switch to SQLite DB'); ?> "<?php echo DB_NAME; ?>"</a>
            <?php
        }
    }
}

$migrate = ob_get_clean();
if (trim($migrate)) {
    ?>
    <div class="card">
        <h2><?php _e('Migrate', 'sqlite-db'); ?></h2>
        <?php echo $migrate; ?> 
    </div>
    <?php
}
?>

<?php
if (defined('DB_CONTENT')) {
    $tmp = FQDBDIR . '*.sqlite';
    $dbs = glob($tmp); //glob($tmp);
    if (!empty($dbs)) {
        if ((!defined('DB_ENGINE') || DB_ENGINE == 'mysql') || count($dbs) > 1 || reset($dbs) != FQDB) {
            ?>
            <div class="card">
                <h2><?php _e('Set SQLite', 'sqlite-db'); ?></h2>
                <p><?php _e('List of available SQLite Databases found on the configured folder*:', 'sqlite-db'); ?><br>
                    <i><?php echo FQDBDIR; ?></i>
                </p>
                <form action="?" method="GET">
                    <input type="hidden" name="page" value="sqlite-db" />
                    <input type="hidden" name="action" value="set" />
                    <select name="db_name">
                        <?php
                        foreach ($dbs as $sdb) {
                            $sdb = str_replace('/', DIRECTORY_SEPARATOR, $sdb);
                            $db_name = basename($sdb, ".sqlite");
                            $selected = (DB_NAME == $db_name) ? ' selected' : '';
                            ?>
                            <option value="<?php echo $db_name; ?>"<?php echo $selected; ?>><?php echo $sdb; ?></option>
                        <?php }
                        ?>
                    </select>
                    <input type="submit" value="<?php _e('Set SQLite DB File', 'sqlite-db'); ?>" class="button">
                </form>

                <p><?php _e('* Hint: you can change default folder, add on wp-config.php file a new constant named \'DB_DIR\', like:', 'sqlite-db'); ?><br>
                    <i>define('DB_DIR', '<?php echo FQDBDIR; ?>');</i></p>

            </div>
            <?php
        }
    }

    if (defined('DB_ENGINE') && DB_ENGINE == 'sqlite') {
        ?>
        <div class="card">
            <h2><?php _e('Backup', 'sqlite-db'); ?></h2>
            <p><?php _e('Create a backup copy of current SQLite DB in the server, you can optionally restore later if needed.', 'sqlite-db'); ?></p>
            <form action="?" method="GET">
                <input type="hidden" name="page" value="sqlite-db" />
                <input type="hidden" name="action" value="clone" />
                <input type="text" name="db_name" placeholder="<?php echo DB_NAME . '_' . time(); ?>">
                <input type="submit" value="<?php _e('Create a backup of current DB', 'sqlite-db'); ?>" class="button">
            </form>
            <p><?php _e('Or Download a copy of the DB on your device:', 'sqlite-db'); ?></p>
            <a href="?page=sqlite-db&action=download" class="button"><?php _e('Download current SQLite DB', 'sqlite-db'); ?> (<?php echo DB_NAME; ?>)</a>
        </div>

        <?php if (defined('SQLITE_LOG') || WP_DEBUG) { ?>
            <div class="card">
                <h2><?php _e('Debug', 'sqlite-db'); ?></h2>
                <p><?php _e('Store all the executed Query: a sql.log file will be generated in the database folder, it could increase a lot its filesize, so enable only when needed', 'sqlite-db'); ?></p>
                <a href="?page=sqlite-db&action=debug" class="button button-primary button-warning"><?php if (defined('SQLITE_LOG') && SQLITE_LOG) {
                _e('Disable Debug');
            } else {
                _e('Enable Debug');
            } ?></a>
                <p><?php _e('* Hint: you can manage it manually, add on wp-config.php file a new constant named \'SQLITE_LOG\' with the wanted log file name, like:', 'sqlite-db'); ?><br>
                    <i>define('SQLITE_LOG', 'query');</i></p>
            </div>
        <?php
        }
        
        ?>
        <div class="card">
            <h2><?php _e('Test Query Translator', 'sqlite-db'); ?></h2>
            <p><?php _e('Simulate the execution of a SQL query and check the translated SQLite query.', 'sqlite-db'); ?></p>
            <form action="?" method="GET">
                <input type="hidden" name="page" value="sqlite-db" />
                <input type="hidden" name="action" value="translate" />
                <input type="text" name="sql" placeholder="SELECT * FROM wp_post WHERE ...">
                <input type="submit" value="<?php _e('Translate', 'sqlite-db'); ?>" class="button">
            </form>
        </div>
        <?php
    }
}

if (false && !empty($dbs)) {
    ?>
    <div class="card">
        <h2><?php _e('Explore SQLite', 'sqlite-db'); ?></h2>
        <p><?php _e('There are many SQLite DBs available in the defined folder, so you can read and edit them easily.', 'sqlite-db'); ?></p>
        <a href="?page=sqlite-db&section=explorer" class="button"><?php _e('Explore SQLite DBs', 'sqlite-db'); ?></a>
    </div>
    <?php
}
?>

<style>
    #wpbody-content {
        display: flex;
        flex-wrap: wrap;
    }
    .notice {
        width: 100%;
        margin: 20px 0;
        margin-right: 20px;
    }
    .card {
        margin-right: 20px;
    }
</style>