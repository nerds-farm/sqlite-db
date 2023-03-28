<div class="card">
    <?php if (!defined('DATABASE_TYPE') || DATABASE_TYPE == 'mysql') { ?>
        <a href="?page=sqlite-db&action=clone" class="button">1. Clone current DB from MySQL to SQLite</a>
    <?php } ?>
    <br><br>
    <?php
    $tmp = FQDBDIR . '*.sqlite';
    //var_dump($tmp);
    $dbs = glob($tmp); //glob($tmp);
    if (!empty($dbs)) {
        if (count($dbs) > 1 || reset($dbs) != FQDB) {
        ?>
        <form action="?" method="GET">
            <input type="hidden" name="page" value="sqlite-db" />
            <input type="hidden" name="action" value="set" />
            <input type="submit" value="2. Set SQLite DB File" class="button">
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
        </form>
        <?php
        }
        ?>
        
        <a href="?page=sqlite-db&section=explorer" class="button">Explore SQLite DBs</a>
        <br><br>
        <?php
        if (defined('DATABASE_TYPE') && DATABASE_TYPE == 'sqlite') {
            if ( !defined( 'WP_SETUP_CONFIG' ) ) {
                define( 'WP_SETUP_CONFIG', true ); 
                
                $success = false;
                if ( function_exists( 'mysqli_connect' ) ) {
                    $dbh = @mysqli_init();
                    @mysqli_real_connect( $dbh, $host, DB_USER, DB_PASSWORD );
                    if ( !$dbh->connect_errno ) {
                        //@$success = mysqli_select_db( $dbh, DB_NAME );
                        $sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="'.DB_NAME.'"';
                        $query = $dbh->query($sql);
                        $row = $query->fetch_object();
                        $success = (bool) $row->exists;
		    }
                    //$success = @new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                } else {
                    @$dbh = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
                    if ($dbh) {
                        $success = @mysql_select_db( DB_NAME, $dbh );
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
                    <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning"><?php _e('Revert to MySQL DB'); ?> - <?php echo DB_NAME; ?></a>
                    <?php
                }
            }
        } else {
            if (file_exists(FQDB)) {
            ?>
            <a href="?page=sqlite-db&action=switch" class="button button-primary button-warning">3. <?php _e('Switch to SQLite DB'); ?> - <?php echo DB_NAME; ?></a>
            <?php
            }
        }
    }
    ?>
</div>