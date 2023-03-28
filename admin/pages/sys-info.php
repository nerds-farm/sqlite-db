<div class="wrap" id="sqlite-admin-wrap">
                <h2><?php _e('System Information', $domain) ?></h2>
                <h3><?php _e('PHP Informations', $domain) ?></h3>
                <?php $info = $this->get_system_info(); ?>
                <table class="widefat page fixed" cellspacing="0" id="sys-info">
                    <thead>
                        <tr>
                            <th class="item"><?php _e('Items', $domain); ?></th>
                            <th><?php _e('Description', $domain); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="item"><?php _e('WordPress Version', $domain); ?></td>
                            <td><?php echo $info['WordPress']; ?></td>
                        </tr>
                        <tr>
                            <td class="item"><?php _e('PHP Version', $domain); ?></td>
                            <td><?php echo $info['PHP']; ?></td>
                        </tr>
                        <tr>
                            <td class="item"><?php _e('PDO Support', $domain); ?></td>
                            <td><?php $this->get_module_setting('PDO', 'PDO support'); ?></td>
                        </tr>
                        <tr>
                            <td class="item"><?php _e('PDO Drivers', $domain); ?></td>
                            <td><?php $this->get_module_setting('PDO', 'PDO drivers'); ?></td>
                        </tr>
                        <tr>
                            <td class="item"><?php _e('PDO Driver for SQLite 3.x', $domain); ?></td>
                            <td><?php $this->get_module_setting('pdo_sqlite', 'PDO Driver for SQLite 3.x'); ?></td>
                        </tr>
                        <tr>
                            <td class="item"><?php _e('SQLite Library Version', $domain); ?></td>
                            <td><?php $this->get_module_setting('pdo_sqlite', 'SQLite Library'); ?>
                        </tr>
                    </tbody>
                </table>

                <?php if (defined('DATABASE_TYPE') && 'DATABASE_TYPE' == 'sqlite' && defined('DB_SQLITE') ) { ?>
                <h3><?php _e('Your Database Status', $domain) ?></h3>
                <table class="widefat page fixed" cellspacing="0" id="status">
                    <thead>
                        <tr>
                            <th><?php _e('Items', $domain) ?></th>
                            <th><?php _e('Status', $domain) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $status = $this->get_database_status(); ?>
                        <tr>
                            <td><?php _e('Database Size', $domain); ?></th>
                            <td><?php echo $status['size']; ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Page Size', $domain); ?></td>
                            <td><?php echo $status['pagesize']; ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Total Number of Pages', $domain); ?></td>
                            <td><?php echo $status['page']; ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Unused Page', $domain) ?></td>
                            <td><?php echo $status['unused']; ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Integrity Check', $domain); ?></td>
                            <td><?php echo strtoupper($status['integrity']); ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Encoding', $domain); ?></th>
                            <td><?php echo $status['encoding']; ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Collations', $domain); ?></th>
                            <td>
                                <?php
                                $i = 0;
                                foreach ($status['collations'] as $col) {
                                    if ($i != 0)
                                        echo '<br />';
                                    echo ($i + 1) . '. ' . $col;
                                    $i++;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Compile Options', $domain); ?></td>
                            <td>
                                <?php
                                $i = 0;
                                foreach ($status['options'] as $op) {
                                    if ($i != 0)
                                        echo '<br />';
                                    echo ($i + 1) . '. ' . $op;
                                    $i++;
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3><?php _e('Database Tables and Indexes', $domain) ?></h3>
                <p>
                    <?php _e('Table names in brown are required by WordPress, and those in blue are created by some plugins. The table sqlite_sequence is not a WordPress table but a table required by SQLite to store the current autoincremented value of each table, which is displayed in the parenthesis after the table names. You can\'t manipulate the tables or indexes here. Please use SQLite utilities (e.g. SQLiteManager).', $domain) ?>
                </p>
                <table class="widefat page fixed" cellspacing="0" id="sqlite-table">
                    <thead>
                        <tr>
                            <th data-sort='{"key":"tblName"}' class="tbl-name"><?php _e('Table Name', $domain) ?></th>
                            <th data-sort='{"key":"which"}' class="tbl_owner"><?php _e('System/User', $domain) ?>
                            <th class="tbl_index"><?php _e('Index ( Column )', $domain) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        global $wpdb;
                        $table_info = $this->get_tables_info();
                        $table_seq = $this->get_sequence();
                        $network_tables = array();
                        if (is_multisite()) {
                            $tmp_tables = $wpdb->tables('blog', false);
                            $blogs = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}blogs");
                            if (count($blogs) > 1) {
                                foreach ($blogs as $id) {
                                    if ($id == 1)
                                        continue;
                                    foreach ($tmp_tables as $tmp_tbl) {
                                        $network_tables[] = $wpdb->prefix . $id . '_' . $tmp_tbl;
                                    }
                                }
                            }
                        }
                        foreach ($table_info as $tbl_name => $index) :
                            ?>
                            <?php
                            if (in_array($tbl_name, $wpdb->tables('all', true)) || in_array($tbl_name, $network_tables) || $tbl_name == 'sqlite_sequence') {
                                $which_table = 'system';
                            } else {
                                $which_table = 'user';
                            }
                            echo '<tr data-table=\'{"tblName":"' . $tbl_name . '","which":"' . $which_table . '"}\'>';
                            if (array_key_exists($tbl_name, $table_seq))
                                $tbl_name .= " ($table_seq[$tbl_name])";
                            echo '<td class="' . $which_table . '">' . $tbl_name . '</td>';
                            echo '<td class="' . $which_table . '">' . $which_table . ' table</td>';
                            ?>
                        <td class="<?php echo $which_table ?>"><?php
                foreach ($index as $idx) {
                    echo $idx . '<br />';
                }
                            ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php } ?>
                
                
                
                <br>
                <hr>
                <hr>
                <?php
                switch (DATABASE_TYPE) {
                    case 'sqlite':
                        $this->sqlite_info();
                        break;
                    case 'mysql':
                    default:
                        $this->mysql_info();
                        break;
                }

                if (!empty($web_server)) {
                    ?>
                    <div class="card mt-4">
                        <div class="card-header">
                <?php _e('Web server'); ?>
                        </div>
                        <ul class="list-group list-group-flush">
                <?php if (!empty($web_server)) { ?>
                                {% if web_server.software is not null %}
                                <li class="list-group-item">
                                    {{ web_server.software }}
                                </li>
                                {% endif %}

                                <li class="list-group-item">
                    <?php _e('PHP extension'); ?>:
                                    {% for extension in web_server.php_extensions %}
                                    {{ extension }}
                                    {{ show_php_docu('book.' ~ extension ~ '.php') }}
                                    {% endfor %}
                                </li>
                                <li class="list-group-item">
                                <?php _e('PHP version'); ?>: <?php echo phpversion(); ?>
                                </li>
                <?php } ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>
                
                
                
            </div>

            <?php /*
              <div class="wrap" id="sqlite-admin-side-wrap">
              <h2><?php _e('Plugin Info', $domain)?></h2>
              <p>
              <?php _e('This table shows plugins you have installed and their compatibility.', $domain)?>
              </p>
              <table class="widefat page fixed" cellspacing="0" id="plugins-info">
              <thead>
              <tr>
              <th data-sort='{"key":"name"}' class="installed-plugins"><?php _e('Installed Plugins', $domain)?></th>
              <th data-sort='{"key":"active"}' class="active-plugins"><?php _e('Active/Inactive', $domain)?></th>
              <th data-sort='{"key":"comp"}' class="compatible"><?php _e('Compatible', $domain)?></th>
              </tr>
              </thead>
              <tbody>
              <?php //$this->show_plugins_info();?>
              </tbody>
              </table>
              <p>
              <?php _e('"Checked*" with an asterisk is from the users\' information. I didn\'t check myself yet. If you found any malfunctioning, please let me know.', $domain);?>
              </p>
              </div>
             */ ?>