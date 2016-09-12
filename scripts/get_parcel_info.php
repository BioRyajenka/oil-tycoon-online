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

if (!isset($_SESSION["user_id"])) die;

mySQLQuery("
SELECT fi.cell_id, fi.x, fi.y, fi.owner_id, fi.land_cost, fi.oil_sell_cost, fi.oil_amount, fi.image_name, f1.type as 'facility1type', f2.type as 'facility2type', f3.type as 'facility3type', f4.type as 'facility4type' FROM 
field fi
JOIN
facilities f1
JOIN
facilities f2
JOIN
facilities f3
JOIN
facilities f4
on f1.id=fi.facility1_id AND f2.id=fi.facility2_id AND f3.id=fi.facility3_id AND f4.id=fi.facility4_id AND fi.x=$x AND fi.y=$y
", function ($result) {
        if ($result == null) return;
        $userId = $_SESSION["user_id"];
        /** @noinspection PhpUndefinedMethodInspection */
        $result = $result->fetch_assoc();
        $cellId = $result['cell_id'];

        if ($result['owner_id'] != $userId) {
            $uk = getUserKnowledgeOil($userId, $cellId);
            if (is_null($uk)) {
                $uk['amount'] = null;
                $uk['discovered'] = null;
            }
            $result['oil_amount'] = $uk['amount'];
            $result['oil_discovered'] = $uk['discovered'];
        }

        $playerInfo = getPlayerInfo($result['owner_id']);
        $result['owner_nickname'] = is_null($playerInfo) ? null : $playerInfo['nickname'];
        $result['owner_color'] = getPlayerColor($result['owner_id']);

        echo json_encode($result);
    });