<?php
$pages = [
  'doc' => __('Documentation', 'sqlite-db'),
  'sys-info' => __('System Info', 'sqlite-db'),
  'setting-file' => __('Miscellaneous', 'sqlite-db'),
  'patch' => __('Patch Utility', 'sqlite-db'),
  'maintenance' => __('Maintenance', 'sqlite-db'),
  'explorer' => __('Explorer', 'sqlite-db'),
  'tuning' => __('Tuning', 'sqlite-db'),
];
?>
<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
    <?php foreach ($pages as $pkey => $title) { ?>
        <a href="<?php echo admin_url(); ?>options-general.php?page=<?php echo $pkey; ?>" class="nav-tab<?php echo (!empty($_GET['page']) && $_GET['page'] == $pkey) ? ' nav-tab-active' : ''; ?>" aria-current="page"><?php echo $title; ?></a>
    <?php
    }
    ?>
</nav>