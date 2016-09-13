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
	// remembering previous answer, because of convenience
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

function getUserKnowledgeOil($cellId) {
	$userId = $_SESSION['user_id'];
	// todo: update scout task
	return mySQLQuery("SELECT * FROM user_knowledge_oil WHERE user_id='$userId' AND cell_id='$cellId'", function ($result) {
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc();
	});
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
	return mySQLQuery("SELECT user_id FROM user_credentials WHERE login='$login'", function ($result) {
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc()['user_id'];
	});
}

function getParcelInfo($x, $y) {
	// TODO: update oil extraction task && construction task
	return mySQLQuery("
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
", function ($result) {
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc();
	});
}

function getFacilityInfo($facilityId) {
	return mySQLQuery("SELECT type, level, data FROM facilities WHERE id='$facilityId'", function ($result) {
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc();
	});
}

function getCurrentPlayerMoney() {
	$userId = $_SESSION['user_id'];
	return mySQLQuery("SELECT money FROM user_gamedata WHERE user_id='$userId'", function ($result) {
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc()['money'];
	});
}

function setCurrentPlayerMoney($newMoney) {
	$userId = $_SESSION['user_id'];
	mySQLQuery("UPDATE user_gamedata SET money='$newMoney' WHERE user_id='$userId'", null);
}

function updateFacility($facilityId, $newType, $newLevel) {
	mySQLQuery("UPDATE facilities SET type='$newType', level='$newLevel' WHERE id='$facilityId'", null);
}

function getFacilityParameters($type, $level) {
	useSecondaryDatabase();
	return mySQLQuery("SELECT cost, data FROM facility_parameters WHERE type='$type' AND level='$level'", function($result) {
		usePrimaryDatabase();
		/** @noinspection PhpUndefinedMethodInspection */
		return $result->fetch_assoc();
	});
}

/* ================ timed tasks ================ */

function startConstructionTask() {

}

function startScoutTask() {

}