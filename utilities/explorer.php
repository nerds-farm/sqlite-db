<?php
include_once SQLITE_DB_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'navigation.php';
?>
<div class="wrap" id="sqlite-explorer">
<h2>Explorer</h2>
<?php
include_once SQLITE_DB_PATH.DIRECTORY_SEPARATOR.'utilities'.DIRECTORY_SEPARATOR.'phpliteadmin'.DIRECTORY_SEPARATOR.'phpliteadmin.php';
?>
</div>
<style>
    .body_tbl {
        display: flex;
    }
    .left_td {
        position: relative;
        background-color: white;
        padding: 10px;
        margin-right: 20px;

    }
    .left_td > .dashicons-admin-collapse {
        position: absolute;
        right: 10px;
        cursor: pointer;
    }
    .left_td.collapsed > .dashicons-admin-collapse {
        right: 0;
        transform: rotate(180deg);
    }
    .left_td fieldset {
        margin: 15px 0;
    }
    .right_td {
        width: 100%;
        overflow: auto;
    }
    #wpfooter {
        position: static;
    }
</style>