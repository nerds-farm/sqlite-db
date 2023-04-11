<?php

if (!function_exists('mysql_get_server_info')) {
    function mysql_get_server_info() {
        include_once(__DIR__.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'utils.php');
        return "SQLite server version: ".\SQLiteDB\Utils::get_sqlite_version();
    }
}
if (!function_exists('mysql_get_client_info')) {
    function mysql_get_client_info() {
        include_once(__DIR__.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'utils.php');
        return \SQLiteDB\Utils::get_sqlite_version();
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

// source: Laravel Framework
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}