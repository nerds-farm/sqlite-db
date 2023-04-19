<div class="card">
    <?php
    if (defined('DB_CONTENT')) {
        ?>
        <h2>Disable db.php</h2>
        <p>If you want to totally disable the SQLite feature and plugin you have to remove the db.php file on your /wp-content folder.</p>
        <a class="button components-button is-secondary is-destructive" href="?page=sqlite-db&action=remove" class="button">Remove db.php automatically</a>
        <p>Hint: you can remove it manually, comment or remove the include plugin row into the /wp-content/db.php file</p>

        <?php
        if (defined('DATABASE_TYPE') && DATABASE_TYPE == 'sqlite') {
            ?>
            <p style="color: red;">WARNING: You actually are running under a SQLite Database, if you disable the plugin you will return to standard MySQL DB, but if the configuration on wp-config.php file is not correct it can cause an irreversible error.</p>
            <?php
        }
    } else {
        ?>
        <h2>Install db.php</h2>
        <p>You need to install the db.php file on your /wp-content folder to let the magic happens.</p>
        <a class="button button-primary" href="?page=sqlite-db&action=install" class="button">Install db.php automatically</a>
        <p>Hint: you can install it manually, copy the file db.copy.php from inside plugin this folder into the /wp-content folder and rename it in db.php</p>
        <?php
    }
    ?>
</div>

<div class="card">
    <h2>Create</h2>
    <p>Add a NEW and EMPTY SQLite DB, a new Fresh Wordpress installation is needed if then you enable it.</p>
    <form action="?" method="GET">
        <input type="hidden" name="page" value="sqlite-db" />
        <input type="hidden" name="action" value="create" />
        <input type="text" name="db_name" placeholder="New SQlite DB name" required>
        <p class="small">Use a clean name, no spaces or special chars are allowed</p>
        <input type="submit" value="Create new Empty DB File" class="button">
    </form>
</div>


<?php
ob_start();
?>
        <?php if (!defined('DATABASE_TYPE') || DATABASE_TYPE == 'mysql') { ?>
            <p>You are actually running your WP from a MySQL Database, so you can create a copy of current MySQL DB to a new SQLite to use later:</p>
            <a href="?page=sqlite-db&action=migrate" class="button">Clone current DB from MySQL to SQLite</a>
        <?php } ?>

<?php if (defined('DB_CONTENT')) { ?>
        <?php
        if (defined('DATABASE_TYPE') && DATABASE_TYPE == 'sqlite') {
            /* TODO
              //https://github.com/vwbusguy/sqlite-to-mysql/blob/master/python/sqlite3-to-mysql.py
              <p>You are actually running your WP from a SQLite Database, so you can create a copy of current SQLite DB to a new MySQL to use later:</p>
              <a href="?page=sqlite-db&action=migrate" class="button">Clone current DB from MySQL to SQLite</a>
             */
        }

        if (defined('DATABASE_TYPE') && DATABASE_TYPE == 'sqlite') {
            if (!defined('WP_SETUP_CONFIG')) {
                define('WP_SETUP_CONFIG', true);

                $success = false;
                if (function_exists('mysqli_connect')) {
                    $dbh = @mysqli_init();
                    @mysqli_real_connect($dbh, $host, DB_USER, DB_PASSWORD);
                    if (!$dbh->connect_errno) {
                        //@$success = mysqli_select_db( $dbh, DB_NAME );
                        $sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="' . DB_NAME . '"';
                        $query = $dbh->query($sql);
                        $row = $query->fetch_object();
                        $success = (bool) $row->exists;
                    }
                    //$success = @new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                } else {
                    @$dbh = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
                    if ($dbh) {
                        $success = @mysql_select_db(DB_NAME, $dbh);
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
                    <p>You are actually running your WP from a SQLite Database, which have same Name of an existent MySQL DB, so you can now switch and return back to MySQL:</p>
                    <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning"><?php _e('Revert to MySQL DB'); ?> - <?php echo DB_NAME; ?></a>
                    <?php
                }
            }
        } else {
            if (file_exists(FQDB)) {
                ?>
                <p>You are actually running your WP from a MySQL Database, but there is already a copy of it (same Name), so you can now switch to SQLite:</p>
                <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning"><?php _e('Switch to SQLite DB'); ?> "<?php echo DB_NAME; ?>"</a>
                <?php
            }
        }
}

$migrate = ob_get_clean();
if (trim($migrate)) {
    ?>
      <div class="card">
        <h2>Migrate</h2>
        <?php echo $migrate; ?> 
    </div>
        <?php 
}
?>
    
<?php if (defined('DB_CONTENT')) {
    $tmp = FQDBDIR . '*.sqlite';
    $dbs = glob($tmp); //glob($tmp);
    if (!empty($dbs)) {
        if ((!defined('DATABASE_TYPE') || DATABASE_TYPE == 'mysql') || count($dbs) > 1 || reset($dbs) != FQDB) {
            ?>
            <div class="card">
                <h2>Set SQLite</h2>
                <p>List of available SQLite Databases found on the configured folder*:<br>
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
                    <input type="submit" value="Set SQLite DB File" class="button">
                </form>

                <p>* Hint: you can change default folder, add on wp-config.php file a new constant named 'DB_DIR', like:<br>
                    <i>define('DB_DIR', '<?php echo FQDBDIR; ?>');</i></p>

            </div>
            <?php
        }
    }
    
    if (defined('DATABASE_TYPE') && DATABASE_TYPE == 'sqlite') {
    ?>
    <div class="card">
        <h2>Backup</h2>
        <p>Create a backup copy of current SQLite DB, you can optionally restore later if needed.</p>
        <form action="?" method="GET">
            <input type="hidden" name="page" value="sqlite-db" />
            <input type="hidden" name="action" value="clone" />
            <input type="text" name="db_name" placeholder="<?php echo DB_NAME . '_' . time(); ?>">
            <input type="submit" value="Create a backup of current DB" class="button">
        </form>
    </div>
                
    <?php if (defined('SQLITE_LOG') || WP_DEBUG) { ?>
    <div class="card">
        <h2>Debug</h2>
        <p>Store all the executed Query: a sql.log file will be generated in the database folder, it could increase a lot its filesize, so enable only when needed</p>
        <a href="?page=sqlite-db&action=debug" class="button button-primary button-warning"><?php if (defined('SQLITE_LOG') && SQLITE_LOG) { _e('Disable Debug'); } else { _e('Enable Debug'); } ?></a>
        <p>* Hint: you can manage it manually, add on wp-config.php file a new constant named 'SQLITE_LOG'with the wanted log file name, like:<br>
                    <i>define('SQLITE_LOG', 'query');</i></p>
    </div>
    <?php }
    }
}

if (!empty($dbs)) {
    ?>
    <div class="card">
        <h2>Explore SQLite</h2>
        <p>There are many SQLite DBs available in the defined folder, so you can read and edit them easily.</p>
        <a href="?page=sqlite-db&section=explorer" class="button">Explore SQLite DBs</a>
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