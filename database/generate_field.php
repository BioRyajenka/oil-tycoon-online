<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 07.09.2016
 * Time: 23:29
 */

require "../scripts/mysql.php";
require "../scripts/perlin.php";

const WIDTH = 15;
const HEIGHT = 15;

const OIL_PER_CELL = 500;
const AVERAGE_OIL_COST = 1;
//const AVERAGE_PARCEL_COST = 10;

function getImagePathFromSettings($settings) {
    $num = null;
    $prob = mt_rand() / mt_getrandmax();
    foreach ($settings as $k => $v) {
        $prob -= $v;
        if ($prob < 0) {
            $num = $k;
            break;
        }
    }
    if (is_null($num)) {
        throw new Exception("bad image path settings array");
    }
    return $num;
}

function generateFieldImageTypes($settings) {
    $res = array();
    for ($i = 0; $i < WIDTH; $i++) {
        for ($j = 0; $j < HEIGHT; $j++) {
            $res[$i][$j] = getImagePathFromSettings($settings);
        }
    }
    return $res;
}

/**
 * @return PerlinNoiseGenerator
 */
function generatePerlinNoise() {
    $perlin = new PerlinNoiseGenerator();
    $perlin->generate([
        PerlinNoiseGenerator::SIZE => max(WIDTH, HEIGHT),
        PerlinNoiseGenerator::PERSISTENCE => 1,
        PerlinNoiseGenerator::GRAIN => 3
    ]);
    return $perlin;
}

function generateOilAmountDistribution() {
    $res = array();
    $perlin = generatePerlinNoise();
    $expectedOil = WIDTH * HEIGHT * OIL_PER_CELL;
    $actualOil = $perlin->getSummaryValue();
    $factor = $expectedOil / $actualOil;

    $map = $perlin->getResult();
    for ($i = 0; $i < WIDTH; $i++) {
        for ($j = 0; $j < HEIGHT; $j++) {
            $res[$i][$j] = $map[$i][$j] * $factor;
        }
    }

    echo "oil amount distribution: <br>" . $perlin->toImageHtmlTag() . "<br><br>";
    return $res;
}

function generateOilSellCostDistribution() {
    $res = array();
    $perlin = generatePerlinNoise();
    $expectedAverageCost = AVERAGE_OIL_COST;
    $actualAverageCost = $perlin->getSummaryValue() / WIDTH / HEIGHT;
    $factor = $expectedAverageCost / $actualAverageCost;

    $map = $perlin->getResult();
    for ($i = 0; $i < WIDTH; $i++) {
        for ($j = 0; $j < HEIGHT; $j++) {
            $res[$i][$j] = $map[$i][$j] * $factor;
        }
    }

    echo "oil sell cost distribution: <br>" . $perlin->toImageHtmlTag() . "<br><br>";
    return $res;
}

/**
 * @param $locked boolean should it be locked initially
 * @return int created facility id
 */
function publishFacility($locked) {
    mySQLQuery("INSERT INTO facilities (type) VALUES ('" . ($locked ? "locked" : "none") . "')", null);
    return mySQLQuery("SELECT LAST_INSERT_ID()", function ($result) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $result->fetch_row()[0];
    });
}

function publishField($fieldImageTypes, $oilAmountDistribution, $oilSellCostDistribution) {
    mySQLQuery("SET FOREIGN_KEY_CHECKS=0", null);
    mySQLQuery("TRUNCATE facilities", null);
    mySQLQuery("TRUNCATE field", null);

    echo "tables truncated successfully<br>";

    for ($i = 0; $i < WIDTH; $i++) {
        for ($j = 0; $j < HEIGHT; $j++) {
            $fid1 = publishFacility(false);
            $fid2 = publishFacility(false);
            $fid3 = publishFacility(false);
            $fid4 = publishFacility(false);
            mySQLQuery("INSERT INTO field 
    (x, y, oil_sell_cost, oil_amount, image_name, facility1_id, facility2_id, facility3_id, facility4_id) VALUES
    ('$i', '$j', '{$oilSellCostDistribution[$i][$j]}', '{$oilAmountDistribution[$i][$j]}', '{$fieldImageTypes[$i][$j]}', 
    '$fid1', '$fid2', '$fid3', '$fid4')", null);
        }
    }
    echo "field published successfully<br>";
}

$imagePathSettings = array(
    "field0.png" => .2,
    "field1.png" => .4,
    "field2.png" => .4
);

$fieldImageTypes = generateFieldImageTypes($imagePathSettings);
$oilAmountDistribution = generateOilAmountDistribution();
$oilSellCostDistribution = generateOilSellCostDistribution();

publishField($fieldImageTypes, $oilAmountDistribution, $oilSellCostDistribution);