<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 15.09.2016
 * Time: 4:11
 */

require "../scripts/mysql.php";
require_once "max_level.php";

useSecondaryDatabase();
mySQLQuery("TRUNCATE facility_timings", null);
for ($i = 1; $i <= MAX_LEVEL; $i++) {
	$constuctionTime = 30 + 300 * ($i - 1);
	mySQLQuery("INSERT INTO facility_timings (level, research_time, construction_time) 
					   VALUES ('$i', '100', '$constuctionTime')", null);
}

echo "success";