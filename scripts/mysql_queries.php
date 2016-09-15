<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 10.09.2016
 * Time: 6:27
 */

require_once "mysql.php";
require_once "log.php";

// TODO: check result for null everywhere

function getMapSize() {
	// remembering previous answer, because of convenience
	static $res = null;

	if (is_null($res)) {
		$res = mySQLQueryRow("SELECT MAX(x) AS width, MAX(y) AS height FROM field");
	}
	return $res;
}

function getPlayerInfo($userId) {
	return mySQLQueryRow("SELECT * FROM user_credentials WHERE user_id='$userId'");
}

function getPlayerColor($userId) {
	return mySQLQueryRow("SELECT * FROM user_gamedata WHERE user_id='$userId'")['color'];
}

function getUserKnowledgeOil($cellId) {
	$userId = $_SESSION['user_id'];
	// todo: update scout task
	return mySQLQueryRow("SELECT * FROM user_knowledge_oil WHERE user_id='$userId' AND cell_id='$cellId'");
}

function getCurrentPlayerDemesne() {
	$userId = $_SESSION['user_id'];
	return mySQLQuery("SELECT x, y FROM field WHERE owner_id='$userId'", function ($qResult) {
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
	mySQLQuery("UPDATE field SET owner_id='$userId' WHERE x='$x' AND y='$y'", null);
}

function getUserIdByLogin($login) {
	return mySQLQueryRow("SELECT user_id FROM user_credentials WHERE login='$login'")['user_id'];
}

function getParcelInfo($x, $y) {
	// TODO: update oil extraction task && construction task
	return mySQLQueryRow("
SELECT fi.cell_id, fi.x, fi.y, fi.owner_id, fi.land_cost, fi.oil_sell_cost, fi.oil_amount, fi.image_name,
	f1.id as 'facility1id', f2.id as 'facility2id', f3.id as 'facility3id', f4.id as 'facility4id',
	f1.type as 'facility1type', f2.type as 'facility2type', f3.type as 'facility3type', f4.type as 'facility4type',
	f1.level as 'facility1level', f2.level as 'facility2level', f3.level as 'facility3level', f4.level as 'facility4level'
FROM 
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
");
}

function getFacilityInfo($facilityId) {
	return mySQLQueryRow("SELECT type, level, data FROM facilities WHERE id='$facilityId'");
}

function getCurrentPlayerMoney() {
	$userId = $_SESSION['user_id'];
	return mySQLQueryRow("SELECT money FROM user_gamedata WHERE user_id='$userId'")['money'];
}

function setCurrentPlayerMoney($newMoney) {
	$userId = $_SESSION['user_id'];
	mySQLQuery("UPDATE user_gamedata SET money='$newMoney' WHERE user_id='$userId'", null);
}

function updateFacilityImmediately($facilityId, $newType, $newLevel) {
	mySQLQuery("UPDATE facilities SET type='$newType', level='$newLevel' WHERE id='$facilityId'", null);
}

function destroyFacility($facilityId) {
	debug("destroying facility $facilityId");
	mySQLQuery("UPDATE facilities SET type='none' WHERE id='$facilityId'", null);
}

function getFacilityParameters($type, $level) {
	useSecondaryDatabase();
	$res = mySQLQueryRow("SELECT cost, data FROM facility_parameters WHERE type='$type' AND level='$level'");
	usePrimaryDatabase();
	return $res;
}

function getData($table, $id) {
	return json_decode(mySQLQueryRow("SELECT data FROM $table WHERE id='$id'")['data']);
}

function updateData($table, $id, $key, $value) {
	$data = getData($table, $id);
	if ($data == null) {
		$data = [];
	}
	$data[$key] = $value;
	$data = json_encode($data);
	mySQLQuery("INSERT INTO $table (data) VALUES ('$data') WHERE id='$id'", null);
}

/* ================ timings ================ */

function getMySQLTime() {
	return mySQLQueryRow("SELECT NOW()")['NOW()'];
}

function processTimestamp($stamp) {
	debug("stamp: " . var_export($stamp, true));
	$stampId = $stamp['id'];
	$progress = getTimestampProgress($stamp);
	$data = json_decode($stamp['data'], true);
	switch ($stamp['type']) {
		case 'facility_update':
			debug("pcti: $progress");
			if ($progress !== 'finished') return;
			debug("wtf: " . json_encode($progress != 'finished'));
			$fid = $stamp['facility_trigger'];
			$type = $data['type'];
			$level = $data['level'];
			updateFacilityImmediately($fid, $type, $level);
			mySQLQuery("DELETE FROM timestamps WHERE id='$stampId'", null);
			break;
		case 'scout':
			if ($progress != 'finished') return;
			break;
		case 'oil_extraction':
			break;
	}
}

function ensureParcelTimestamps($x, $y) {
	$facilities = mySQLQueryRow("SELECT facility1_id, facility2_id, facility3_id, facility4_id FROM field WHERE x='$x' AND y='$y'");
	foreach ($facilities as $k => $v) {
		mySQLQuery("SELECT * FROM timestamps WHERE facility_trigger='$v'", function ($result) {
			/** @noinspection PhpUndefinedMethodInspection */
			while ($stamp = $result->fetch_assoc()) {
				processTimestamp($stamp);
			}
		});
	}
	$parcelId = mySQLQueryRow("SELECT cell_id FROM field WHERE x='$x' AND y='$y'")['cell_id'];
	$stamp = mySQLQueryRow("SELECT * FROM timestamps WHERE parcel_trigger='$parcelId'");
	if ($stamp != null) {
		processTimestamp($stamp);
	}
}

function getFacilityConstructionProgress($facilityId) {
	$stamp = mySQLQueryRow("SELECT * FROM timestamps WHERE facility_trigger='$facilityId'");
	// if it is in construction, there are only one timestamp associated with it
	if ($stamp == null || $stamp['type'] != 'facility_update') {
		return "finished";
	}
	return getTimestampProgress($stamp); //number_format($res, 0, '.', '')
}

function getTimestampProgress($stamp) {
	$currentTime = strtotime(getMySQLTime());
	$startTime = strtotime($stamp['start_time']);
	$duration = $stamp['duration'];
	debug("progress: $currentTime $startTime $duration");
	$res = min(($currentTime - $startTime) / $duration, 1);
	if ($res == 1) {
		return "finished";
	}
	debug("progress res: $res");
	return $res;
}

function getConstructionTime($level) {
	useSecondaryDatabase();
	$res = mySQLQueryRow("SELECT construction_time FROM facility_timings WHERE level='$level'")['construction_time'];
	usePrimaryDatabase();
	return $res;
}

function updateFacilityViaTimestamp($facilityId, $newType, $newLevel) {
	// TODO: ensure there are no other timestamps
	$data = json_encode(['type' => $newType, 'level' => $newLevel]);
	$duration = getConstructionTime($newLevel);
	mySQLQuery("INSERT INTO timestamps (type, data, facility_trigger, duration) 
					   VALUES ('facility_update', '$data', '$facilityId', '$duration')", null);
}