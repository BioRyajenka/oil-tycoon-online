<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 09.09.2016
 * Time: 0:12
 */

require "mysql.php";
require "mysql_queries.php";

session_start();

$x = $_GET["x"];
$y = $_GET["y"];
//$ssid = $_GET["SESSION_ID"];
$ssid = session_id();
$userId = $_SESSION["user_id"];

mySQLQuery("SELECT * FROM field WHERE x='$x' AND y='$y'", function ($result) {
    if ($result == null) return;
    global $userId;

    /** @noinspection PhpUndefinedMethodInspection */
    $result = $result->fetch_assoc();
    $cellId = $result['cell_id'];
    $uk = getUserKnowledgeOil($userId, $cellId);

    if (is_null($uk)) {
        $uk['amount'] = "undiscovered";
        $uk['discovered'] = null;
    }
    $result['oil_amount'] = $uk['amount'];
    $result['oil_discovered'] = $uk['discovered'];

    $playerInfo = getPlayerInfo($result['owner_id']);
    $result['ownerNickname'] = is_null($playerInfo) ? 'nobody' : $playerInfo['nickname'];

    echo json_encode($result);
});