<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 10.09.2016
 * Time: 22:30
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * session timeout in seconds
 */
const SESSION_TIMEOUT_SEC = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT_SEC)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    // TODO: check if session_id is set
    header("Location: /login.php");
    die;
}

//TODO: logout