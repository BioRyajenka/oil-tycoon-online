<?php
require "mysql.php";

session_start();

class AuthStatus {
    const UNAUTHORIZED = "UNAUTHORIZED";
    const INCORRECT_SPELLING = "INCORRECT_SPELLING";
    const OPERATION_FAILURE = "OPERATION_FAILURE";
    const OPERATION_SUCCESS = "OPERATION_SUCCESS";
}

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
require "mysql_queries.php";

function login($login, $password) {
    if (!checkAuthPairSpelling($login, $password)) {
        return AuthStatus::INCORRECT_SPELLING;
    }
    $login = strtolower($login);
    if (!checkAuthPair($login, $password)) {
        return AuthStatus::OPERATION_FAILURE;
    }
    loginWithoutChecks($login, $password);
    return AuthStatus::OPERATION_SUCCESS;
}

function loginWithoutChecks($login, $password) {
    mySQLQuery("SELECT user_id FROM user_credentials WHERE login='$login' AND password='$password'",
        function ($result) {
            /** @noinspection PhpUndefinedMethodInspection */
            $user_id = $result->fetch_assoc()['user_id'];
            // note that result fetched from mysql is string (not int)
            $_SESSION['user_id'] = $user_id;
            $ssid = session_id();
            mySQLQuery("UPDATE user_credentials SET session_id='$ssid' WHERE user_id='$user_id'", null);
        });
}

function register($login, $password, $nickname, $email, $gender) {
    if (!checkAuthPairSpelling($login, $password) || !checkNicknameSpelling($nickname)
        || !checkEmailSpelling($email) || !checkGenderSpelling($gender)) {
        return AuthStatus::INCORRECT_SPELLING;
    }
    $login = strtolower($login);
    if (checkLoginExists($login)) {
        return AuthStatus::OPERATION_FAILURE;
    }
    registerWithoutChecks($login, $password, $nickname, $email, $gender);
    return AuthStatus::OPERATION_SUCCESS;
}

function registerWithoutChecks($login, $password, $nickname, $email, $gender) {
    mySQLQuery("INSERT INTO user_credentials (login, password, nickname, email, gender)
        VALUES ('$login', '$password', '$nickname', '$email', '$gender')", null);

    // TODO: it's temp, remove it
    for ($i = 0; $i < 3; $i++) {
        $x = rand(0, getMapSize()['width']);
        $y = rand(0, getMapSize()['height']);
        $userId = getUserIdByLogin($login);
        require_once "logger.php";
        debug_log("acquired $x $y");
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