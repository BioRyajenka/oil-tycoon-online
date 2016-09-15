<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 14.09.2016
 * Time: 1:29
 */

require "../scripts/mysql.php";
require_once "max_level.php";

useSecondaryDatabase();

function createSiloParameters() {
	$res = [];
	for ($level = 1; $level <= MAX_LEVEL; $level++) {
		$row = [
			"type" => "silo",
			"level" => $level,
			"cost" => $level * $level * 10 + 20,
			"data" => json_encode(['max_value' => 10 + ($level - 1) * 30])
		];
		$res[] = $row;
	}
	return $res;
}

function createRigParameters() {
	$res = [];
	for ($level = 1; $level <= MAX_LEVEL; $level++) {
		$row = [
			"type" => "rig",
			"level" => $level,
			"cost" => $level * $level * 10 + 20,
			// speed in barrels/hour
			"data" => json_encode(['speed' => $level])
		];
		$res[] = $row;
	}
	return $res;
}

function createScoutDepotParameters() {
	$res = [];
	for ($level = 1; $level <= MAX_LEVEL; $level++) {
		$row = [
			"type" => "scout depot",
			"level" => $level,
			"cost" => $level * $level * 10 + 20,
			"data" => json_encode([
				// move speed in parcels/hour
				'moveSpeed' => (500 * $level - 350) / 30,
				// time on operation in hours
				'timeOnOperation' => 0.00184513 * $level * $level - 0.108923 * $level + 1.60708,
				'scouts' => $level < 7 ? 1 : $level < 17 ? 2 : $level < 27 ? 3 : 4
			])
		];
		$res[] = $row;
	}
	return $res;
}

function createTransportDepotParameters() {
	$res = [];
	for ($level = 1; $level <= MAX_LEVEL; $level++) {
		$row = [
			"type" => "transport depot",
			"level" => $level,
			"cost" => $level * $level * 10 + 20,
			"data" => json_encode([
				// speed in parcels/hour. here we consider that transport
				// goes only in one side and then teleportates back
				'speed' => (500 * $level - 350) / 50,
				'distance' => 3 + $level
			])
		];
		$res[] = $row;
	}
	return $res;
}

function publishFacilityParameters() {
	$rows = createSiloParameters();
	$rows = array_merge($rows, createRigParameters());
	$rows = array_merge($rows, createRigParameters());
	$rows = array_merge($rows, createScoutDepotParameters());
	$rows = array_merge($rows, createTransportDepotParameters());

	mySQLQuery("TRUNCATE facility_parameters", null);
	foreach ($rows as $row) {
		$type = $row['type'];
		$level = $row['level'];
		$cost = $row['cost'];
		$data = $row['data'];
		mySQLQuery("INSERT INTO facility_parameters (type, level, cost, data) VALUES 
			('$type', '$level', '$cost', '$data')", null);
	}
}

publishFacilityParameters();

echo "success";