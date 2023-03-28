<?php
$pages = [
  'sqlite-db-doc' => __('Documentation', 'sqlite-db'),
  'sqlite-db-sys-info' => __('System Info', 'sqlite-db'),
  'sqlite-db-setting-file' => __('Miscellaneous', 'sqlite-db'),
  'sqlite-db-patch' => __('Patch Utility', 'sqlite-db'),
  'sqlite-db-maintenance' => __('Maintenance', 'sqlite-db'),
  'sqlite-db-explorer' => __('Explorer', 'sqlite-db'),
  'sqlite-db-tuning' => __('Tuning', 'sqlite-db'),
];
?>
<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
    <?php foreach ($pages as $pkey => $title) { ?>
        <a href="<?php menu_page_url($pkey, true); ?>" class="nav-tab<?php echo (!empty($_GET['page']) && $_GET['page'] == $pkey) ? ' nav-tab-active' : ''; ?>" aria-current="page"><?php echo $title; ?></a>
    <?php
    }
    ?>
</nav>