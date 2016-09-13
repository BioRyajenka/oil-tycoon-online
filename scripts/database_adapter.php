<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 09.09.2016
 * Time: 0:12
 */

namespace database_adapter;

require "mysql.php";
require "mysql_queries.php";
require "session_guard.php";

$aliases = [
	'test' => 'test',
	'get_parcel_info' => 'getParcelInfo',
	'try_build' => 'tryBuild',
	'try_upgrade' => 'tryUpgrade',
	'money' => 'getMoney'
];

function processRequest() {
	global $aliases;
	if (!isset($_GET['method'])) return;
	$method = $_GET['method'];
	if (isset($aliases[$method])) {
		$path = __NAMESPACE__ . "\\" . $aliases[$method];
		if (!function_exists($path)) {
			echo "Should define method '" . $aliases[$method] . "' first";
			die;
		}
		echo json_encode(call_user_func($path));
	} else {
		echo "null";
	}
}

/*=============== requests ===============*/

function test() {
	return $_GET["x"];
}

function tryBuild() {
	//TODO: ensure owner
	$facilityId = $_GET["facility_id"];
	$newType = $_GET["type"];
	$money = getCurrentPlayerMoney();
	$facilityParameters = getFacilityParameters($facilityId, 1);
	$cost = $facilityParameters['cost'];
	if ($cost > $money) {
		return null;
	}
	setCurrentPlayerMoney($money - $cost);
	updateFacility($facilityId, $newType, 1);

	require_once "logger.php";
	debug_log("$facilityId	$newType");
	return "success";
}

function tryUpgrade() {
	//TODO: ensure owner
	$facilityId = $_GET["facility_id"];
	$facilityInfo = getFacilityInfo($facilityId);
	$level = $facilityInfo['level'];
	$type = $facilityInfo['type'];
	if ($type == 'locked' || $type == 'none' || $level == 30) {
		return null;
	}
	$money = getCurrentPlayerMoney();
	$facilityParameters = getFacilityParameters($facilityId, $level + 1);
	$cost = $facilityParameters['cost'];
	if ($cost > $money) {
		return null;
	}
	setCurrentPlayerMoney($money - $cost);
	updateFacility($facilityId, $type, $level + 1);
	return "success";
}

function getMoney() {
	return getCurrentPlayerMoney();
}

function getParcelInfo() {
	$x = $_GET["x"];
	$y = $_GET["y"];
	$userId = $_SESSION["user_id"];
	$result = \getParcelInfo($x, $y);
	if ($result == null) return null;
	if ($result['owner_id'] != $userId) {
		$uk = getUserKnowledgeOil($result['cell_id']);
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

	return $result;
}

/*===============  ===============*/

processRequest();