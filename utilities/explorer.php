<?php
include_once SQLITE_DB_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'navigation.php';
?>
<div class="wrap" id="sqlite-explorer">
<h2><?php _e('Explorer', 'sqlite-db'); ?></h2>
<?php
include_once SQLITE_DB_PATH.DIRECTORY_SEPARATOR.'utilities'.DIRECTORY_SEPARATOR.'phpliteadmin'.DIRECTORY_SEPARATOR.'phpliteadmin.php';
?>
</div>
<?php
wp_enqueue_style('phpliteamin', SQLITE_DB_URL.'utilities/phpliteadmin/css/phpliteadmin.css');
wp_enqueue_script('phpliteamin', SQLITE_DB_URL.'utilities/phpliteadmin/js/phpliteadmin.js');