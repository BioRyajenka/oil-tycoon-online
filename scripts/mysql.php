<?php
const MYSQL_HOST = 'localhost';
const MYSQL_USER = 'root';
const MYSQL_PASS = 'thi7ong4';
const DATABASE_NAME = 'oil_tycoon';

function _createConnection() {
    $mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, DATABASE_NAME);
    /* проверка соединения */
    if ($mysqli->connect_errno) {
        printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
        exit();
    }
    return $mysqli;
}

/**
 * @param $query string query
 * @param $consumer callable function which takes query result as it's single argument
 * @return boolean|void result of calling user-provided <b>consumer()</b> function
 */

function mySQLQuery($query, $consumer) {
    global $mysqli;
    // if it's never opened or closed
    /** @noinspection PhpUndefinedMethodInspection */
    if (!isset($mysqli) || !$mysqli->ping()) {
        $mysqli = _createConnection();
    }
    $result = $mysqli->query($query) or die("error '{$mysqli->error}' in query '$query'");
    if (!is_null($consumer)) {
        return $consumer($result);
    }
    if (!is_bool($result)) {
        // $result may be true for queries like SELECT etc.
        $result->close();
    }
    //$mysqli->close();
    return null;
}