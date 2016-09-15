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
require_once "log.php";

$aliases = [
	'test' => 'test',
	'get_parcel_info' => 'getParcelInfo',
	'try_build' => 'tryBuild',
	'try_upgrade' => 'tryUpgrade',
	'money' => 'getMoney',
	'destroy' => 'destroyFacility'
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
	//TODO: ensure owner and timings and previous type (should be none)
	$facilityId = $_GET["facility_id"];
	$newType = $_GET["type"];
	$money = getCurrentPlayerMoney();
	$facilityParameters = getFacilityParameters($newType, 1);
	$cost = $facilityParameters['cost'];
	if ($cost > $money) {
		return;
	}
	setCurrentPlayerMoney($money - $cost);
	updateFacilityViaTimestamp($facilityId, $newType, 1);
}

function tryUpgrade() {
	//TODO: ensure owner and timings
	$facilityId = $_GET["facility_id"];
	$facilityInfo = getFacilityInfo($facilityId);
	$level = $facilityInfo['level'];
	$type = $facilityInfo['type'];
	if ($type == 'locked' || $type == 'none' || $level == 30) {
		return;
	}
	$money = getCurrentPlayerMoney();
	$facilityParameters = getFacilityParameters($type, $level + 1);
	$cost = $facilityParameters['cost'];
	if ($cost > $money) {
		return;
	}

	setCurrentPlayerMoney($money - $cost);
	updateFacilityViaTimestamp($facilityId, $type, $level + 1);
}

function destroyFacility() {
	//TODO: ensure owner and timings
	$facilityId = $_GET["facility_id"];
	\destroyFacility($facilityId);
}

function getMoney() {
	return getCurrentPlayerMoney();
}

function getParcelInfo() {
	$x = $_GET["x"];
	$y = $_GET["y"];
	$userId = $_SESSION["user_id"];
	\ensureParcelTimestamps($x, $y);
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
	} else {
		for ($i = 1; $i <= 4; $i++) {
			$result["facility$i" . "construction_progress"] =
				getFacilityConstructionProgress($result["facility$i" . "id"]);
		}
	}

	$playerInfo = getPlayerInfo($result['owner_id']);
	$result['owner_nickname'] = is_null($playerInfo) ? null : $playerInfo['nickname'];
	$result['owner_color'] = getPlayerColor($result['owner_id']);

	return $result;
}

/*===============  ===============*/

processRequest();