<?php

if (!function_exists('mysql_get_server_info')) {
    function mysql_get_server_info() {
        return "SQLite server version: ".\SQLite_DB\Utils::get_sqlite_version();
    }
}
//- Support functions
// for php < 5.6.0
if (!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if (strlen($str1) != strlen($str2))
            return false;
        else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for ($i = strlen($res) - 1; $i >= 0; $i--)
                $ret |= ord($res[$i]);
            return !$ret;
        }
    }
}