<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 07.09.2016
 * Time: 1:27
 */

namespace {
	function clearLog() {
		file_put_contents(log\LOG_FILE_PATH, "");
		error_log(log\getPrefix() . "*clear*\n", 3, log\LOG_FILE_PATH);
	}

	function debug($text) {
		$text = log\getPrefix() . $text;

		error_log($text . "\n", 3, log\LOG_FILE_PATH);
	}
}

namespace log {
	const LOG_FILE_PATH = "C:/oil tycoon/log.txt";

	function getPrefix() {
		$source = $lst = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)['1'];
		return "(" . date("i:s") . "|" . basename($source['file'], ".php") . ":" . $source['line'] . "): ";
	}
}