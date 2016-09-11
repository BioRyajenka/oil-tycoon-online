<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 10.09.2016
 * Time: 20:21
 */

require "mysql_queries.php";

echo json_encode(getPlayerInfo($_GET['user_id']));