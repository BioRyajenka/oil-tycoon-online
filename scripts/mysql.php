<?php

namespace mysql {
	const MYSQL_HOST = 'localhost';
	const MYSQL_USER = 'root';
	const MYSQL_PASS = 'thi7ong4';
	const PRIMARY_DATABASE_NAME = 'oil_tycoon';
	const SECONDARY_DATABASE_NAME = 'oil_tycoon_world_parameters';

	function createMySQLConnection($database = PRIMARY_DATABASE_NAME) {
		$mysqli = new \mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, $database);
		/* проверка соединения */
		if ($mysqli->connect_errno) {
			printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
			exit();
		}
		return $mysqli;
	}
}

namespace {
	function usePrimaryDatabase() {
		global $mysqli;
		$mysqli = mysql\createMySQLConnection(\mysql\PRIMARY_DATABASE_NAME);
	}

	function useSecondaryDatabase() {
		global $mysqli;
		$mysqli = mysql\createMySQLConnection(\mysql\SECONDARY_DATABASE_NAME);
	}

	function mySQLQueryRow($query) {
		return mySQLQuery($query, function ($result) {
			/** @noinspection PhpUndefinedMethodInspection */
			return $result->fetch_assoc();
		});
	}

	/**
	 * @param $query string query
	 * @param $consumer callable function which takes query result as it's single argument
	 * @return boolean|mysqli_result result of calling user-provided <b>consumer()</b> function
	 */
	function mySQLQuery($query, $consumer) {
		global $mysqli;

		/* @var $mysqli mysqli */
		if (!isset($mysqli) || !$mysqli->ping()) { // i.e. if it's never opened or closed
			$mysqli = mysql\createMySQLConnection();
		}
		$result = $mysqli->query($query) or die("error '{$mysqli->error}' in query '$query'");

		if (!is_null($consumer)) {
			return $consumer($result, $query);
		}
		if (!is_bool($result)) {
			// $result may be true for queries like SELECT etc.
			$result->close();
		}
		return null;
	}
}