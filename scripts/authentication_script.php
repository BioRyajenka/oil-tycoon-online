<?php
/**
 * @param $login
 * Must start with letter
 * 6-31 characters
 * Letters and numbers only
 * @param $password
 * Should contain 6-31 letters
 * @return bool
 * returns true if loggining successful and false otherwise
 */
namespace {
	require "mysql.php";

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	class AuthStatus {
		const UNAUTHORIZED = "UNAUTHORIZED";
		const INCORRECT_SPELLING = "INCORRECT_SPELLING";
		const OPERATION_FAILURE = "OPERATION_FAILURE";
		const OPERATION_SUCCESS = "OPERATION_SUCCESS";
	}

	require "mysql_queries.php";

	function login($login, $password) {
		if (!authentication\checkAuthPairSpelling($login, $password)) {
			return AuthStatus::INCORRECT_SPELLING;
		}
		$login = strtolower($login);
		if (!authentication\checkAuthPair($login, $password)) {
			return AuthStatus::OPERATION_FAILURE;
		}
		authentication\loginWithoutChecks($login, $password);
		return AuthStatus::OPERATION_SUCCESS;
	}

	function register($login, $password, $nickname, $email, $gender) {
		if (!authentication\checkAuthPairSpelling($login, $password) || !authentication\checkNicknameSpelling($nickname)
			|| !authentication\checkEmailSpelling($email) || !authentication\checkGenderSpelling($gender)
		) {
			return AuthStatus::INCORRECT_SPELLING;
		}
		$login = strtolower($login);
		if (authentication\checkLoginExists($login)) {
			return AuthStatus::OPERATION_FAILURE;
		}
		authentication\registerWithoutChecks($login, $password, $nickname, $email, $gender);
		return AuthStatus::OPERATION_SUCCESS;
	}
}

namespace authentication {
	function loginWithoutChecks($login, $password) {
		mySQLQuery("SELECT user_id FROM user_credentials WHERE login='$login' AND password='$password'",
			function ($result) {
				/** @noinspection PhpUndefinedMethodInspection */
				$userId = $result->fetch_assoc()['user_id'];
				// note that result fetched from mysql is string (not int)
				$_SESSION['user_id'] = $userId;
				//$ssid = session_id();
				//mySQLQuery("UPDATE user_credentials SET session_id='$ssid' WHERE user_id='$user_id'", null);
			});
	}

	function registerWithoutChecks($login, $password, $nickname, $email, $gender) {
		mySQLQuery("INSERT INTO user_credentials (login, password, nickname, email, gender)
        VALUES ('$login', '$password', '$nickname', '$email', '$gender')", null);
		$userId = getUserIdByLogin($login);
		$color = generatePlayerColor();

		mySQLQuery("INSERT INTO user_gamedata 
(user_id, money, maxlevel_silo, maxlevel_transport_depot, maxlevel_scouting_depot, maxlevel_rig, transport_speed, researched_unique_technologies, color) VALUES 
('$userId', 100, 1, 1, 1, 1, 1, '', '$color')", null);

		// TODO: it's temp, remove it
		for ($i = 0; $i < 3; $i++) {
			$x = rand(0, getMapSize()['width']);
			$y = rand(0, getMapSize()['height']);
			acquireParcel($userId, $x, $y);
		}
	}

// checks

	function checkLoginExists($login) {
		return mySQLQuery("SELECT * FROM user_credentials WHERE login='$login'",
			function ($result) {
				return $result->num_rows == 1;
			});
	}

	function checkAuthPair($login, $password) {
		return mySQLQuery("SELECT * FROM user_credentials WHERE login='$login' AND password='$password'",
			function ($result) {
				return $result->num_rows == 1;
			});
	}

	function checkAuthPairSpelling($login, $password) {
		return checkLoginSpelling($login) && checkPasswordSpelling($password);
	}

	function checkLoginSpelling($login) {
		return preg_match('/^[A-Za-z]{1}[A-Za-z0-9]{6,31}$/', $login);
	}

	function checkPasswordSpelling($password) {
		return preg_match('/^.{6,31}$/', $password);
	}

	function checkNicknameSpelling($nickname) {
		return preg_match('/^[a-z]\w{5,14}$/i', $nickname);
	}

	function checkEmailSpelling($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	function checkGenderSpelling($gender) {
		return $gender === 'male' || $gender === 'female';
	}
}