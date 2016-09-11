<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 07.09.2016
 * Time: 1:27
 */

const LOG_FILE_PATH = "C:/oil tycoon/log.txt";

function clear_log() {
    file_put_contents(LOG_FILE_PATH, "");
}

function debug_log($text) {
    error_log($text."\n", 3, LOG_FILE_PATH);
}