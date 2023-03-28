<div class="wrap single" id="sqlite-admin-wrap">
                <h2><?php _e('Welcome to SQLite Integration', 'sqlite-db') ?></h2>
                <p>
            <?php _e('Thank you for using SQLite Integration plugin!', 'sqlite-db') ?>
                </p>
                <p>
            <?php _e('You read this message, which means you have succeeded in installing WordPress with this plugin SQLite DB. Congratulations and enjoy your Blogging!', 'sqlite-db') ?>
                </p>
                <p>
                    <?php _e('You don\'t have to set any special settings. In fact there are no other settings. You can write articles or pages and customize you WordPress in an ordinary way. You want to install your plugins? All right, go ahead. But some of them may be incompatible with this. Please read more information about this plugin and your SQLite database below.', 'sqlite-db') ?>
                </p>
                <p><?php _e('Deactivation makes this documents and utilities disappear from the dashboard, but it doesn\'t affect the functionality of the SQLite Integration. when uninstalled, it will remove wp-content/uploads/patches directory (if exists) and wp-content/db.php file altogether.', 'sqlite-db'); ?></p>
                
                <?php include_once SQLITE_DB_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'navigation.php'; ?>
                
                
                <table class="widefat" cellspacing="0" id="menu">
                    <thead>
                    <th><?php _e('Title', 'sqlite-db'); ?></th>
                    <th><?php _e('Contents', 'sqlite-db'); ?></th>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="menu-title"><a href="<?php echo $this->show_parent(); ?>?page=doc"><?php _e('Documentation', 'sqlite-db') ?></a></td>
                            <td><?php _e('You can read documentation about this plugin and plugin compatibility.', 'sqlite-db') ?></td>
                        </tr>
                        <tr>
                            <td class="menu-title"><a href="<?php echo $this->show_parent(); ?>?page=sys-info"><?php _e('System Info', 'sqlite-db') ?></a></td>
                            <td><?php _e('You can see database and system information.', 'sqlite-db') ?></td>
                        </tr>
                        <tr>
                            <td class="menu-title"><a href="<?php echo $this->show_parent(); ?>?page=setting-file"><?php _e('Miscellaneous', 'sqlite-db') ?></a></td>
                            <td><?php _e('You can see the error log and edit db.php file (if necessary) and optimize your database.', 'sqlite-db') ?></td>
                        </tr>
                        <tr>
                            <td><a href="<?php echo $this->show_parent(); ?>?page=patch"><?php _e('Patch Utility', 'sqlite-db') ?></a></td>
                            <td><?php _e('You can upload patch files and apply them to the incompatible plugins.', 'sqlite-db') ?></td>
                        </tr>
                        <tr>
                            <td><a href="<?php echo $this->show_parent(); ?>?page=maintenance"><?php _e('Maintenance', 'sqlite-db'); ?></a></td>
                            <td><?php _e('You can check your database and fix it if needed.', 'sqlite-db'); ?></td>
                        </tr>
                        <tr>
                            <td><a href="<?php echo $this->show_parent(); ?>?page=explorer"><?php _e('Explorer', 'sqlite-db'); ?></a></td>
                            <td><?php _e('You can read, edit and execute Custom SQL on your DB.', 'sqlite-db'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>