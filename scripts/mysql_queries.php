<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 10.09.2016
 * Time: 6:27
 */

require_once "mysql.php";

// TODO: check result for null everywhere

function getMapSize() {
    // remembering previous answer, because of convenience reasons
    static $res = null;

    if (is_null($res)) {
        $res = mySQLQuery("SELECT MAX(x) AS width, MAX(y) AS height FROM field", function ($result) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $result->fetch_assoc();
        });
    }
    return $res;
}

function getPlayerInfo($userId) {
    return mySQLQuery("SELECT * FROM user_credentials WHERE user_id='$userId'", function ($result) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $result->fetch_assoc();
    });
}

function getPlayerColor($userId) {
    return mySQLQuery("SELECT * FROM user_gamedata WHERE user_id='$userId'", function ($result) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $result->fetch_assoc()['color'];
    });
}

function getUserKnowledgeOil($userId, $cellId) {
    return mySQLQuery("SELECT * FROM user_knowledge_oil WHERE user_id='$userId' AND cell_id='$cellId'", function ($result) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $result->fetch_assoc();
    });
}

function getCurrentPlayerDemesne() {
    $user_id = $_SESSION['user_id'];
    return mySQLQuery("SELECT x, y FROM field WHERE owner_id='$user_id'", function ($qResult) {
        $res = array();
        /** @noinspection PhpUndefinedMethodInspection */
        while ($res[] = $qResult->fetch_assoc()) ;
        array_pop($res);
        foreach ($res as $k => $v) {
            $res[$k] = array(
                'x' => intval($v['x']),
                'y' => intval($v['y'])
            );
        }
        return $res;
    });
}

function acquireParcel($userId, $x, $y) {
    return mySQLQuery("UPDATE field SET owner_id='$userId' WHERE x='$x' AND y='$y'", null);
}

function getUserIdByLogin($login) {
    return mySQLQuery("SELECT user_id FROM user_credentials WHERE login='$login'", function ($result) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $result->fetch_assoc()['user_id'];
    });
}

function getFacilities($x, $y) {

}