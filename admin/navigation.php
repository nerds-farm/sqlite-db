<?php
$pages = [
  'dashboard' => __('Dashboard', 'sqlite-db'),
//  'info' => __('System Info', 'sqlite-db'),
//  'setting' => __('Miscellaneous', 'sqlite-db'),
//  'patch' => __('Patch Utility', 'sqlite-db'),
//  'maintenance' => __('Maintenance', 'sqlite-db'),
//  'explorer' => __('Explorer', 'sqlite-db'),
//  'tuning' => __('Tuning', 'sqlite-db'),
];
$tmp = FQDBDIR . '*.sqlite';
$dbs = glob($tmp);
if (!empty($dbs)) {
    $pages['explorer'] = __('Explorer', 'sqlite-db');
}
if (defined('DB_ENGINE') && DB_ENGINE == 'sqlite') {
    $pages['info'] = __('System Info', 'sqlite-db');
}
?>
<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu" style="width: 100%;">
    <?php foreach ($pages as $pkey => $title) { ?>
        <a href="<?php menu_page_url('sqlite-db', true); ?>&section=<?php echo $pkey; ?>" class="nav-tab<?php echo (empty($_GET['section']) && 'dashboard' == $pkey) || (!empty($_GET['section']) && $_GET['section'] == $pkey) ? ' nav-tab-active' : ''; ?>" aria-current="page"><?php echo $title; ?></a>
    <?php } ?>
</nav>