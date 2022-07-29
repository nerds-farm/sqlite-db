<?php
include_once SQLITE_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'navigation.php';
?>
<div class="wrap" id="sqlite-explorer">
<h2>Explorer</h2>
<?php
include_once SQLITE_PATH.DIRECTORY_SEPARATOR.'utilities'.DIRECTORY_SEPARATOR.'phpliteadmin'.DIRECTORY_SEPARATOR.'phpliteadmin.php';
?>
</div>
<style>
    .body_tbl {
        display: flex;
    }
    .left_td {

    background-color: white;
    padding: 20px;
    margin-right: 20px;

}.left_td fieldset {
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